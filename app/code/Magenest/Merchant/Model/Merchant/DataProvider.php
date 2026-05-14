<?php
/**
 * app/code/Magenest/Merchant/Model/Merchant/DataProvider.php
 *
 * Data provider for the Merchant admin form (UI Component).
 */
declare(strict_types=1);

namespace Magenest\Merchant\Model\Merchant;

use Magenest\Merchant\Api\Data\MerchantInterface;
use Magenest\Merchant\Model\ResourceModel\Merchant\CollectionFactory;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /** @var array<int, array<string, mixed>>|null */
    protected $loadedData;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        private readonly DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        $this->loadedData = [];

        /** @var \Magenest\Merchant\Model\Merchant $merchant */
        foreach ($items as $merchant) {
            $row = $merchant->getData();

            // Multiselect needs to be exposed as an array to the form
            if (!empty($row[MerchantInterface::CATEGORY_IDS]) && !is_array($row[MerchantInterface::CATEGORY_IDS])) {
                $row[MerchantInterface::CATEGORY_IDS] = explode(',', (string) $row[MerchantInterface::CATEGORY_IDS]);
            }

            $this->loadedData[$merchant->getId()] = $row;
        }

        // Restore form data after a save failure
        $persisted = $this->dataPersistor->get('magenest_merchant');
        if (!empty($persisted)) {
            $id = $persisted['entity_id'] ?? null;
            $this->loadedData[$id ?: null] = $persisted;
            $this->dataPersistor->clear('magenest_merchant');
        }

        return $this->loadedData;
    }
}
