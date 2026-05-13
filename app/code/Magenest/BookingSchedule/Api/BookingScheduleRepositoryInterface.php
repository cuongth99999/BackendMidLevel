<?php
/**
 * app/code/Magenest/BookingSchedule/Api/BookingScheduleRepositoryInterface.php
 */
declare(strict_types=1);

namespace Magenest\BookingSchedule\Api;

use Magenest\BookingSchedule\Api\Data\BookingScheduleInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface BookingScheduleRepositoryInterface
{
    /**
     * @throws CouldNotSaveException
     */
    public function save(BookingScheduleInterface $schedule): BookingScheduleInterface;

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): BookingScheduleInterface;

    /**
     * Locate a slot by its (date, time) natural key. Returns null when absent.
     */
    public function getBySlot(string $date, string $time): ?BookingScheduleInterface;

    /**
     * Fetch every slot whose schedule_date is between $from and $to (inclusive),
     * keyed by "Y-m-d|H:i" for O(1) lookup from the frontend.
     *
     * @return BookingScheduleInterface[]
     */
    public function getRange(string $from, string $to): array;
}
