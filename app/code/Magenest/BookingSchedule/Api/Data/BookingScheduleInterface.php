<?php
/**
 * app/code/Magenest/BookingSchedule/Api/Data/BookingScheduleInterface.php
 */
declare(strict_types=1);

namespace Magenest\BookingSchedule\Api\Data;

interface BookingScheduleInterface
{
    public const ENTITY_ID    = 'entity_id';
    public const SCHEDULE_DATE = 'schedule_date';
    public const SCHEDULE_TIME = 'schedule_time';
    public const STOCK         = 'stock';
    public const USED          = 'used';
    public const RESERVATION   = 'reservation';
    public const CREATED_AT    = 'created_at';
    public const UPDATED_AT    = 'updated_at';

    public function getEntityId(): ?int;

    /**
     * @param int|string $entityId Untyped to match Magento\Framework\Model\AbstractModel parent.
     */
    public function setEntityId($entityId): self;

    public function getScheduleDate(): string;

    public function setScheduleDate(string $date): self;

    public function getScheduleTime(): string;

    public function setScheduleTime(string $time): self;

    public function getStock(): int;

    public function setStock(int $stock): self;

    public function getUsed(): int;

    public function setUsed(int $used): self;

    public function getReservation(): int;

    public function setReservation(int $reservation): self;
}
