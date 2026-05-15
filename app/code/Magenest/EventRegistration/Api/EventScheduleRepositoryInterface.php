<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Api;

use Magenest\EventRegistration\Api\Data\EventScheduleInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface EventScheduleRepositoryInterface
{
    public function save(EventScheduleInterface $schedule): EventScheduleInterface;

    public function getById(int $id): EventScheduleInterface;

    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    public function getByEventId(int $eventId): array;

    public function deleteByEventId(int $eventId): void;

    public function delete(EventScheduleInterface $schedule): bool;
}
