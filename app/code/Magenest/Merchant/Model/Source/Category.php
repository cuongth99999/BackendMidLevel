<?php
/**
 * app/code/Magenest/Merchant/Model/Source/Category.php
 *
 * Returns the list of Magento catalog categories as EAV options for the
 * `category_ids` multiselect attribute.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Model\Source;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Category extends AbstractSource
{
    public function __construct(
        private readonly CategoryListInterface $categoryList,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function getAllOptions(): array
    {
        if ($this->_options !== null) {
            return $this->_options;
        }

        $criteria = $this->searchCriteriaBuilder
            ->addFilter(CategoryInterface::KEY_LEVEL, 1, 'gt') // skip Root
            ->create();

        $options = [];
        foreach ($this->categoryList->getList($criteria)->getItems() as $category) {
            $options[] = [
                'value' => (string) $category->getId(),
                'label' => (string) $category->getName(),
            ];
        }

        usort($options, static fn (array $a, array $b): int => strcmp($a['label'], $b['label']));

        $this->_options = $options;
        return $this->_options;
    }
}
