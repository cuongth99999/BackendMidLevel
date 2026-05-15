<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model;

use Magenest\EventRegistration\Api\Data\EventScheduleInterface;
use Magenest\EventRegistration\Model\ResourceModel\EventSchedule as EventScheduleResource;
use Magento\Framework\Model\AbstractModel;

class EventSchedule extends AbstractModel implements EventScheduleInterface
{
    protected $_eventPrefix = 'magenest_event_schedule';

    protected function _construct(): void
    {
        $this->_init(EventScheduleResource::class);
    }

    public function getEntityId(): ?int
    {
        $id = $this->getData(self::ENTITY_ID);
        return $id === null ? null : (int) $id;
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getEventId(): int
    {
        return (int) $this->getData(self::EVENT_ID);
    }

    public function setEventId(int $eventId): EventScheduleInterface
    {
        return $this->setData(self::EVENT_ID, $eventId);
    }

    public function getDayOfWeek(): ?string
    {
        $value = $this->getData(self::DAY_OF_WEEK);
        return $value === null ? null : (string) $value;
    }

    public function setDayOfWeek(string $dayOfWeek): EventScheduleInterface
    {
        return $this->setData(self::DAY_OF_WEEK, $dayOfWeek);
    }

    public function getScheduleDate(): ?string
    {
        $value = $this->getData(self::SCHEDULE_DATE);
        return $value === null ? null : (string) $value;
    }

    public function setScheduleDate(string $scheduleDate): EventScheduleInterface
    {
        return $this->setData(self::SCHEDULE_DATE, $scheduleDate);
    }

    public function getDetailsMessage(): ?string
    {
        $value = $this->getData(self::DETAILS_MESSAGE);
        return $value === null ? null : (string) $value;
    }

    public function setDetailsMessage(?string $detailsMessage): EventScheduleInterface
    {
        return $this->setData(self::DETAILS_MESSAGE, $detailsMessage);
    }

    public function getEventTime(): ?string
    {
        $value = $this->getData(self::EVENT_TIME);
        return $value === null ? null : (string) $value;
    }

    public function setEventTime(?string $eventTime): EventScheduleInterface
    {
        return $this->setData(self::EVENT_TIME, $eventTime);
    }

    public function getPosition(): int
    {
        return (int) $this->getData(self::POSITION);
    }

    public function setPosition(int $position): EventScheduleInterface
    {
        return $this->setData(self::POSITION, $position);
    }
}
