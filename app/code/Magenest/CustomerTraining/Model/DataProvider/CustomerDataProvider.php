<?php
/**
 * app/code/Magenest/CustomerTraining/Model/DataProvider/CustomerDataProvider.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Model\DataProvider;

use Magenest\CustomerTraining\Model\CustomerTraining;
use Magenest\CustomerTraining\Model\ResourceModel\CustomerTraining\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class CustomerDataProvider extends AbstractDataProvider
{
    /**
     * @var array<int|string,array<string,mixed>>|null
     */
    private ?array $loadedData = null;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();

        // PERF: Only load the record being edited rather than the whole table.
        $entityId = (int) $this->request->getParam($this->requestFieldName);
        if ($entityId) {
            $this->collection->addFieldToFilter($this->primaryFieldName, $entityId);
        } else {
            // New entry: empty result set.
            $this->collection->addFieldToFilter($this->primaryFieldName, ['null' => true]);
        }
    }

    public function getData(): array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        /** @var CustomerTraining $item */
        foreach ($this->collection->getItems() as $item) {
            $this->loadedData[$item->getId()] = $item->getData();
        }

        $persisted = $this->dataPersistor->get('magenest_customer_training');
        if (!empty($persisted)) {
            $entityId = (int) ($persisted['entity_id'] ?? 0) ?: 'new';
            $this->loadedData[$entityId] = $persisted;
            $this->dataPersistor->clear('magenest_customer_training');
        }

        return $this->loadedData;
    }
}
