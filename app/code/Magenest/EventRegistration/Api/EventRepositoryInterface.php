<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Api;

use Magenest\EventRegistration\Api\Data\EventInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface EventRepositoryInterface
{
    public function save(EventInterface $event): EventInterface;

    public function getById(int $id): EventInterface;

    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    public function delete(EventInterface $event): bool;

    public function deleteById(int $id): bool;
}
