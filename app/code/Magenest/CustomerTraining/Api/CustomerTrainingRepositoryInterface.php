<?php
/**
 * app/code/Magenest/CustomerTraining/Api/CustomerTrainingRepositoryInterface.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Api;

use Magenest\CustomerTraining\Api\Data\CustomerTrainingInterface;
use Magenest\CustomerTraining\Api\Data\CustomerTrainingSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface CustomerTrainingRepositoryInterface
{
    /**
     * Save a customer training record (create when entity_id is empty, update otherwise).
     *
     * @param \Magenest\CustomerTraining\Api\Data\CustomerTrainingInterface $entity
     * @return \Magenest\CustomerTraining\Api\Data\CustomerTrainingInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(CustomerTrainingInterface $entity): CustomerTrainingInterface;

    /**
     * Load a customer training record by entity ID.
     *
     * @param int $entityId
     * @return \Magenest\CustomerTraining\Api\Data\CustomerTrainingInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $entityId): CustomerTrainingInterface;

    /**
     * Search customer training records by criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magenest\CustomerTraining\Api\Data\CustomerTrainingSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): CustomerTrainingSearchResultsInterface;

    /**
     * Delete a customer training record.
     *
     * @param \Magenest\CustomerTraining\Api\Data\CustomerTrainingInterface $entity
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(CustomerTrainingInterface $entity): bool;

    /**
     * Delete a customer training record by entity ID.
     *
     * @param int $entityId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $entityId): bool;
}
