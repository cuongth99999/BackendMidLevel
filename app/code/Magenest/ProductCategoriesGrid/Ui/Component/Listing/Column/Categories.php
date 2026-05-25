<?php
/**
 * app/code/Magenest/ProductCategoriesGrid/Ui/Component/Listing/Column/Categories.php
 */
declare(strict_types=1);

namespace Magenest\ProductCategoriesGrid\Ui\Component\Listing\Column;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Categories extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly ResourceConnection $resource,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');
        $productIds = array_map('intval', array_column($dataSource['data']['items'], 'entity_id'));
        $categoriesByProduct = $this->loadCategoryNamesByProduct($productIds);

        foreach ($dataSource['data']['items'] as &$item) {
            $productId = (int)($item['entity_id'] ?? 0);
            $names = $categoriesByProduct[$productId] ?? [];
            // SEC: escape each category name before joining into the rendered HTML cell.
            $escaped = array_map(fn ($name) => $this->escaper->escapeHtml($name), $names);
            $item[$fieldName] = $escaped ? implode(', ', $escaped) : '&mdash;';
        }

        return $dataSource;
    }

    /**
     * @param int[] $productIds
     * @return array<int, string[]> productId => [categoryName, ...]
     */
    private function loadCategoryNamesByProduct(array $productIds): array
    {
        if (!$productIds) {
            return [];
        }

        // PERF: single query to fetch all product->category links for the visible page.
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName('catalog_category_product'), ['product_id', 'category_id'])
            ->where('product_id IN (?)', $productIds);
        $links = $connection->fetchAll($select);

        if (!$links) {
            return [];
        }

        $categoryIds = array_unique(array_map('intval', array_column($links, 'category_id')));

        // PERF: one collection load for all distinct categories on the page.
        $categoryCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addFieldToFilter('entity_id', ['in' => $categoryIds]);

        $nameById = [];
        foreach ($categoryCollection as $category) {
            $nameById[(int)$category->getId()] = (string)$category->getName();
        }

        $result = [];
        foreach ($links as $row) {
            $productId = (int)$row['product_id'];
            $categoryId = (int)$row['category_id'];
            if (!isset($nameById[$categoryId])) {
                continue;
            }
            $result[$productId][] = $nameById[$categoryId];
        }

        return $result;
    }
}
