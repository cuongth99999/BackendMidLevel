<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model;

use Magenest\EventRegistration\Api\Data\EventInterface;
use Magenest\EventRegistration\Api\EventRepositoryInterface;
use Magenest\EventRegistration\Model\ResourceModel\Event as EventResource;
use Magenest\EventRegistration\Model\ResourceModel\Event\Collection;
use Magenest\EventRegistration\Model\ResourceModel\Event\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class EventRepository implements EventRepositoryInterface
{
    public function __construct(
        private readonly EventResource $resource,
        private readonly EventFactory $eventFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly SearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    public function save(EventInterface $event): EventInterface
    {
        try {
            $this->resource->save($event);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(__('Could not save the event: %1', $e->getMessage()), $e);
        }
        return $event;
    }

    public function getById(int $id): EventInterface
    {
        $event = $this->eventFactory->create();
        $this->resource->load($event, $id);
        if (!$event->getEntityId()) {
            throw new NoSuchEntityException(__('Event with id "%1" does not exist.', $id));
        }
        return $event;
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

    public function delete(EventInterface $event): bool
    {
        try {
            $this->resource->delete($event);
        } catch (\Throwable $e) {
            throw new CouldNotDeleteException(__('Could not delete the event: %1', $e->getMessage()), $e);
        }
        return true;
    }

    public function deleteById(int $id): bool
    {
        return $this->delete($this->getById($id));
    }
}
