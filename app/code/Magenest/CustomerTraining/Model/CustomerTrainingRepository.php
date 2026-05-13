<?php
/**
 * app/code/Magenest/CustomerTraining/Model/CustomerTrainingRepository.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Model;

use Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface;
use Magenest\CustomerTraining\Api\Data\CustomerTrainingInterface;
use Magenest\CustomerTraining\Api\Data\CustomerTrainingSearchResultsInterface;
use Magenest\CustomerTraining\Api\Data\CustomerTrainingSearchResultsInterfaceFactory;
use Magenest\CustomerTraining\Model\CustomerTrainingFactory;
use Magenest\CustomerTraining\Model\ResourceModel\CustomerTraining as CustomerTrainingResource;
use Magenest\CustomerTraining\Model\ResourceModel\CustomerTraining\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerTrainingRepository implements CustomerTrainingRepositoryInterface
{
    public function __construct(
        private readonly CustomerTrainingResource $resource,
        private readonly CustomerTrainingFactory $modelFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly CustomerTrainingSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    public function save(CustomerTrainingInterface $entity): CustomerTrainingInterface
    {
        try {
            /** @var CustomerTraining $entity */
            $this->resource->save($entity);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(
                __('Could not save customer training entry: %1', $e->getMessage()),
                $e
            );
        }
        return $entity;
    }

    public function getById(int $entityId): CustomerTrainingInterface
    {
        /** @var CustomerTraining $model */
        $model = $this->modelFactory->create();
        $this->resource->load($model, $entityId);
        if (!$model->getEntityId()) {
            throw new NoSuchEntityException(
                __('Customer training entry with ID "%1" does not exist.', $entityId)
            );
        }
        return $model;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): CustomerTrainingSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var CustomerTrainingSearchResultsInterface $results */
        $results = $this->searchResultsFactory->create();
        $results->setSearchCriteria($searchCriteria);
        $results->setItems($collection->getItems());
        $results->setTotalCount($collection->getSize());
        return $results;
    }

    public function delete(CustomerTrainingInterface $entity): bool
    {
        try {
            /** @var CustomerTraining $entity */
            $this->resource->delete($entity);
        } catch (\Throwable $e) {
            throw new CouldNotDeleteException(
                __('Could not delete customer training entry: %1', $e->getMessage()),
                $e
            );
        }
        return true;
    }

    public function deleteById(int $entityId): bool
    {
        return $this->delete($this->getById($entityId));
    }
}
