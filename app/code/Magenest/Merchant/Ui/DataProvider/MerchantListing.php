<?php
/**
 * app/code/Magenest/Merchant/Ui/DataProvider/MerchantListing.php
 *
 * Custom UI listing data-provider for the Merchant EAV grid.
 *
 * Why a custom one: the stock `Magento\Framework\View\Element\UiComponent\
 * DataProvider\DataProvider` runs queries through `Magento\Framework\Api\
 * Search\Reporting`, which builds its select from `$collection->getSelect()`.
 * For an EAV collection that select only contains the entity table columns —
 * value-table data is attached afterwards by `_loadAttributes()`. So the grid
 * ends up with rows whose only field is `entity_id`.
 *
 * Here we iterate the collection (which triggers `_loadAttributes`) and emit
 * each model's full `getData()` payload.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Ui\DataProvider;

use Magenest\Merchant\Model\ResourceModel\Merchant\CollectionFactory;
use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;

class MerchantListing extends AbstractDataProvider
{
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->collection->addAttributeToSelect('*');
    }

    public function getData(): array
    {
        $items = [];
        // Iterating triggers load() → _loadAttributes() so each item carries
        // its EAV values keyed by attribute_code.
        foreach ($this->getCollection()->getItems() as $merchant) {
            $items[] = $merchant->getData();
        }

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items'        => $items,
        ];
    }

    public function addFilter(Filter $filter): void
    {
        $field     = $filter->getField();
        $condition = $filter->getConditionType() ?: 'eq';
        $value     = $filter->getValue();

        // Entity-table columns must use addFieldToFilter directly to avoid
        // EAV trying to resolve them as attributes.
        if (in_array($field, ['entity_id', 'created_at', 'updated_at', 'store_id'], true)) {
            $this->getCollection()->addFieldToFilter($field, [$condition => $value]);
            return;
        }

        $this->getCollection()->addAttributeToFilter($field, [$condition => $value]);
    }

    public function addOrder($field, $direction): void
    {
        if (in_array($field, ['entity_id', 'created_at', 'updated_at', 'store_id'], true)) {
            $this->getCollection()->getSelect()->order(
                sprintf('%s.%s %s', 'e', $field, $direction)
            );
            return;
        }
        $this->getCollection()->addAttributeToSort($field, $direction);
    }
}
