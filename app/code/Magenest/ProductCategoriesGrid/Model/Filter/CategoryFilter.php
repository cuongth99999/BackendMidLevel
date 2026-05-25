<?php
/**
 * app/code/Magenest/ProductCategoriesGrid/Model/Filter/CategoryFilter.php
 * Filter strategy bound to the "magenest_categories" column on the product grid.
 * Handles both single-value and multi-select (array) input.
 */
declare(strict_types=1);

namespace Magenest\ProductCategoriesGrid\Model\Filter;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class CategoryFilter implements AddFilterToCollectionInterface
{
    /**
     * @param Collection $collection
     * @param string $field
     * @param array|null $condition
     */
    public function addFilter(Collection $collection, $field, $condition = null): void
    {
        if (!$collection instanceof ProductCollection || !is_array($condition)) {
            return;
        }

        $value = reset($condition);
        if ($value === false || $value === null || $value === '') {
            return;
        }

        // ui-select multi-select sends an array; built-in select sends a scalar.
        $categoryIds = array_filter(
            array_map('intval', is_array($value) ? $value : [$value]),
            static fn (int $id): bool => $id > 0
        );

        if (!$categoryIds) {
            return;
        }

        // Uses catalog_category_product via the product collection helper.
        // 'in' covers both single-id and multi-id selections.
        $collection->addCategoriesFilter(['in' => $categoryIds]);
    }
}
