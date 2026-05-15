<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model;

use Magenest\EventRegistration\Api\Data\EventScheduleInterface;
use Magenest\EventRegistration\Api\EventScheduleRepositoryInterface;
use Magenest\EventRegistration\Model\ResourceModel\EventSchedule as EventScheduleResource;
use Magenest\EventRegistration\Model\ResourceModel\EventSchedule\Collection;
use Magenest\EventRegistration\Model\ResourceModel\EventSchedule\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class EventScheduleRepository implements EventScheduleRepositoryInterface
{
    public function __construct(
        private readonly EventScheduleResource $resource,
        private readonly EventScheduleFactory $scheduleFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly SearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    public function save(EventScheduleInterface $schedule): EventScheduleInterface
    {
        try {
            $this->resource->save($schedule);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(__('Could not save schedule row: %1', $e->getMessage()), $e);
        }
        return $schedule;
    }

    public function getById(int $id): EventScheduleInterface
    {
        $schedule = $this->scheduleFactory->create();
        $this->resource->load($schedule, $id);
        if (!$schedule->getEntityId()) {
            throw new NoSuchEntityException(__('Schedule row "%1" does not exist.', $id));
        }
        return $schedule;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var SearchResultsInterface $results */
        $results = $this->searchResultsFactory->create();
        $results->setSearchCriteria($searchCriteria);
        $results->setItems($collection->getItems());
        $results->setTotalCount($collection->getSize());
        return $results;
    }

    public function getByEventId(int $eventId): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(EventScheduleInterface::EVENT_ID, $eventId);
        $collection->setOrder(EventScheduleInterface::POSITION, Collection::SORT_ORDER_ASC);
        return $collection->getItems();
    }

    public function deleteByEventId(int $eventId): void
    {
        $connection = $this->resource->getConnection();
        $connection->delete(
            $this->resource->getMainTable(),
            [EventScheduleInterface::EVENT_ID . ' = ?' => $eventId]
        );
    }

    public function delete(EventScheduleInterface $schedule): bool
    {
        try {
            $this->resource->delete($schedule);
        } catch (\Throwable $e) {
            throw new CouldNotDeleteException(__('Could not delete schedule row: %1', $e->getMessage()), $e);
        }
        return true;
    }
}
