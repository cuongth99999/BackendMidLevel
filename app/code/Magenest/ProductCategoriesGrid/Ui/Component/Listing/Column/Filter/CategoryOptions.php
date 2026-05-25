<?php
/**
 * app/code/Magenest/ProductCategoriesGrid/Ui/Component/Listing/Column/Filter/CategoryOptions.php
 * Options for the "Categories" column filter dropdown on the product grid.
 */
declare(strict_types=1);

namespace Magenest\ProductCategoriesGrid\Ui\Component\Listing\Column\Filter;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class CategoryOptions implements OptionSourceInterface
{
    /**
     * @var array<int, array{label: string, value: int}>|null
     */
    private ?array $options = null;

    public function __construct(
        private readonly CategoryCollectionFactory $categoryCollectionFactory
    ) {
    }

    public function toOptionArray(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('name')
            // Exclude admin root catalog (id=1, level=0) — has no products.
            ->addFieldToFilter('entity_id', ['neq' => Category::TREE_ROOT_ID]);

        // `path` is a flat column on catalog_category_entity, not EAV — use setOrder.
        $collection->getSelect()->order('e.path ASC');

        $options = [];
        /** @var Category $category */
        foreach ($collection as $category) {
            $level = max(0, (int)$category->getLevel() - 1);
            $indent = str_repeat('— ', $level);
            $options[] = [
                'value' => (int)$category->getId(),
                'label' => $indent . (string)$category->getName(),
            ];
        }

        return $this->options = $options;
    }
}
