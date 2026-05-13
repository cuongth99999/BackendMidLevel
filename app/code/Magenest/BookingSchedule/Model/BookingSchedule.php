<?php
/**
 * app/code/Magenest/BookingSchedule/Model/BookingSchedule.php
 */
declare(strict_types=1);

namespace Magenest\BookingSchedule\Model;

use Magenest\BookingSchedule\Api\Data\BookingScheduleInterface;
use Magenest\BookingSchedule\Model\ResourceModel\BookingSchedule as BookingScheduleResource;
use Magento\Framework\Model\AbstractModel;

class BookingSchedule extends AbstractModel implements BookingScheduleInterface
{
    protected function _construct(): void
    {
        $this->_init(BookingScheduleResource::class);
    }

    public function getEntityId(): ?int
    {
        $value = $this->getData(self::ENTITY_ID);
        return $value === null ? null : (int) $value;
    }

    public function setEntityId($entityId): BookingScheduleInterface
    {
        return $this->setData(self::ENTITY_ID, (int) $entityId);
    }

    public function getScheduleDate(): string
    {
        return (string) $this->getData(self::SCHEDULE_DATE);
    }

    public function setScheduleDate(string $date): BookingScheduleInterface
    {
        return $this->setData(self::SCHEDULE_DATE, $date);
    }

    public function getScheduleTime(): string
    {
        return (string) $this->getData(self::SCHEDULE_TIME);
    }

    public function setScheduleTime(string $time): BookingScheduleInterface
    {
        return $this->setData(self::SCHEDULE_TIME, $time);
    }

    public function getStock(): int
    {
        return (int) $this->getData(self::STOCK);
    }

    public function setStock(int $stock): BookingScheduleInterface
    {
        return $this->setData(self::STOCK, $stock);
    }

    public function getUsed(): int
    {
        return (int) $this->getData(self::USED);
    }

    public function setUsed(int $used): BookingScheduleInterface
    {
        return $this->setData(self::USED, $used);
    }

    public function getReservation(): int
    {
        return (int) $this->getData(self::RESERVATION);
    }

    public function setReservation(int $reservation): BookingScheduleInterface
    {
        return $this->setData(self::RESERVATION, $reservation);
    }
}
