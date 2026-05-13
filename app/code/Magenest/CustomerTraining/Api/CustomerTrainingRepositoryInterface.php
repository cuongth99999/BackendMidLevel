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
     * @throws CouldNotSaveException
     */
    public function save(CustomerTrainingInterface $entity): CustomerTrainingInterface;

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): CustomerTrainingInterface;

    public function getList(SearchCriteriaInterface $searchCriteria): CustomerTrainingSearchResultsInterface;

    /**
     * @throws CouldNotDeleteException
     */
    public function delete(CustomerTrainingInterface $entity): bool;

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $entityId): bool;
}
