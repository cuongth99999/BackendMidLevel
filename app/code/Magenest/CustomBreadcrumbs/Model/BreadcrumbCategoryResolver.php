<?php
declare(strict_types=1);

namespace Magenest\CustomBreadcrumbs\Model;

use Magenest\CustomBreadcrumbs\Setup\Patch\Data\AddUseAsMainBreadcrumbAttribute;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class BreadcrumbCategoryResolver
{
    public function __construct(
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Deepest active category in $product's category trees (direct OR ancestor)
     * that is flagged use_as_main_breadcrumb = Yes, scoped to the current
     * store's root tree. Returns null when no category in any of the product's
     * trees is flagged.
     */
    public function resolve(ProductInterface $product): ?CategoryInterface
    {
        $directCategoryIds = $product->getCategoryIds();
        if (empty($directCategoryIds)) {
            return null;
        }

        try {
            $store          = $this->storeManager->getStore();
            $storeId        = (int) $store->getId();
            $rootCategoryId = (int) $store->getRootCategoryId();
        } catch (\Exception $e) {
            $this->logger->error('CustomBreadcrumbs: cannot resolve store context: ' . $e->getMessage());
            return null;
        }

        // Collect every ancestor id reachable from the product's direct categories
        // so a flag on e.g. "Tops" still wins when the product is only assigned to
        // the leaf "Tanks".
        $candidateIds = $this->collectAncestorIds($directCategoryIds, $rootCategoryId);
        if (empty($candidateIds)) {
            return null;
        }

        $collection = $this->categoryCollectionFactory->create();
        $collection->setStore($storeId)
            ->addAttributeToSelect(['name', 'url_key', 'url_path', 'is_active'])
            ->addAttributeToFilter('entity_id', ['in' => $candidateIds])
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter(AddUseAsMainBreadcrumbAttribute::ATTRIBUTE_CODE, 1)
            ->addOrder('level', Select::SQL_DESC)
            ->setPageSize(1);

        $deepest = $collection->getFirstItem();
        if (!$deepest->getId()) {
            return null;
        }

        return $deepest;
    }

    /**
     * @param int[]|string[] $categoryIds
     * @return int[]
     */
    private function collectAncestorIds(array $categoryIds, int $rootCategoryId): array
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addFieldToSelect(['path'])
            ->addFieldToFilter('entity_id', ['in' => $categoryIds])
            ->addFieldToFilter('path', ['like' => '1/' . $rootCategoryId . '/%']);

        $ids = [];
        foreach ($collection as $category) {
            foreach (explode('/', (string) $category->getPath()) as $pathId) {
                $pathId = (int) $pathId;
                if ($pathId > $rootCategoryId) {
                    $ids[$pathId] = true;
                }
            }
        }

        return array_keys($ids);
    }

    /**
     * Categories from the store root down to (and including) $leaf, in display order.
     * Skips the global root (id=1) and the store's root category.
     *
     * @return CategoryInterface[]
     */
    public function getCategoryPath(CategoryInterface $leaf): array
    {
        try {
            $storeId        = (int) $this->storeManager->getStore()->getId();
            $rootCategoryId = (int) $this->storeManager->getStore()->getRootCategoryId();
        } catch (\Exception $e) {
            $this->logger->error('CustomBreadcrumbs: cannot resolve store context: ' . $e->getMessage());
            return [];
        }

        $pathIds = array_filter(array_map('intval', explode('/', (string) $leaf->getPath())));

        $crumbs = [];
        foreach ($pathIds as $id) {
            if ($id <= $rootCategoryId) {
                continue;
            }
            try {
                $crumbs[] = $this->categoryRepository->get($id, $storeId);
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        return $crumbs;
    }
}
