<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Controller\Adminhtml\Register;

use Magenest\EventRegistration\Model\Queue\MassRegisterPublisher;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class MassRegister extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenest_EventRegistration::event_register';

    public function __construct(
        Context $context,
        private readonly CustomerCollectionFactory $customerCollectionFactory,
        private readonly MassRegisterPublisher $publisher,
        private readonly JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();
        try {
            $eventId    = (int) $this->getRequest()->getParam('event_id');
            $scheduleId = (int) $this->getRequest()->getParam('schedule_id');
            $note       = trim((string) $this->getRequest()->getParam('note', ''));

            if ($eventId <= 0 || $scheduleId <= 0) {
                throw new LocalizedException(__('Please select both an event and an event time.'));
            }

            $customerIds = $this->resolveCustomerIds();
            if (!$customerIds) {
                throw new LocalizedException(__('No customers were selected.'));
            }

            $batchCount = $this->publisher->publish($eventId, $scheduleId, $note ?: null, $customerIds);

            return $result->setData([
                'success'      => true,
                'batches'      => $batchCount,
                'customers'    => count($customerIds),
                'message'      => (string) __(
                    'Queued %1 customer(s) in %2 batch(es) of up to %3 each. Run the consumer to process them.',
                    count($customerIds),
                    $batchCount,
                    MassRegisterPublisher::BATCH_SIZE
                ),
            ]);
        } catch (LocalizedException $e) {
            return $result->setHttpResponseCode(400)->setData([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            return $result->setHttpResponseCode(500)->setData([
                'success' => false,
                'message' => (string) __('Unexpected error: %1', $e->getMessage()),
            ]);
        }
    }

    /**
     * Resolve grid selection payload into a customer ID list.
     * Supports three modes from the UI:
     *  - include (default): pass user-checked rows → `selected[]`
     *  - exclude: user clicked "Select all" across pages → all customers minus `excluded[]`
     * Honors current grid filters / fulltext search when expanding "select all".
     *
     * @return int[]
     */
    private function resolveCustomerIds(): array
    {
        $mode     = (string) $this->getRequest()->getParam('mode', 'include');
        $selected = (array) $this->getRequest()->getParam('selected', []);
        $excluded = (array) $this->getRequest()->getParam('excluded', []);
        $filters  = (array) $this->getRequest()->getParam('filters', []);
        $search   = (string) $this->getRequest()->getParam('search', '');

        if ($mode !== 'exclude') {
            return array_values(array_unique(array_filter(array_map('intval', $selected))));
        }

        $collection = $this->customerCollectionFactory->create();
        $collection->addAttributeToSelect('entity_id', 'inner');
        $this->applyFilters($collection, $filters);
        $this->applyFulltext($collection, $search);

        $excludedIds = array_values(array_filter(array_map('intval', $excluded)));
        if ($excludedIds) {
            $collection->addFieldToFilter('entity_id', ['nin' => $excludedIds]);
        }

        return array_values(array_map('intval', $collection->getAllIds()));
    }

    private function applyFilters($collection, array $filters): void
    {
        foreach ($filters as $field => $value) {
            if ($value === null || $value === '' || $field === 'placeholder') {
                continue;
            }
            $field = (string) $field;
            if ($field === 'fulltext' || $field === 'store_id') {
                continue;
            }
            $collection->addAttributeToFilter($field, ['like' => '%' . $value . '%']);
        }
    }

    private function applyFulltext($collection, string $search): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }
        $collection->addAttributeToFilter([
            ['attribute' => 'firstname', 'like' => '%' . $search . '%'],
            ['attribute' => 'lastname',  'like' => '%' . $search . '%'],
            ['attribute' => 'email',     'like' => '%' . $search . '%'],
        ]);
    }
}
