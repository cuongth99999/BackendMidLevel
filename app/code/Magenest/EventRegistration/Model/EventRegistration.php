<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model;

use Magenest\EventRegistration\Api\Data\EventRegistrationInterface;
use Magenest\EventRegistration\Model\ResourceModel\EventRegistration as RegistrationResource;
use Magento\Framework\Model\AbstractModel;

class EventRegistration extends AbstractModel implements EventRegistrationInterface
{
    protected $_eventPrefix = 'magenest_event_registration';

    protected function _construct(): void
    {
        $this->_init(RegistrationResource::class);
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

    public function getCustomerId(): int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    public function setCustomerId(int $customerId): EventRegistrationInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    public function getEventId(): int
    {
        return (int) $this->getData(self::EVENT_ID);
    }

    public function setEventId(int $eventId): EventRegistrationInterface
    {
        return $this->setData(self::EVENT_ID, $eventId);
    }

    public function getScheduleId(): int
    {
        return (int) $this->getData(self::SCHEDULE_ID);
    }

    public function setScheduleId(int $scheduleId): EventRegistrationInterface
    {
        return $this->setData(self::SCHEDULE_ID, $scheduleId);
    }

    public function getNote(): ?string
    {
        $value = $this->getData(self::NOTE);
        return $value === null ? null : (string) $value;
    }

    public function setNote(?string $note): EventRegistrationInterface
    {
        return $this->setData(self::NOTE, $note);
    }

    public function getStatus(): string
    {
        return (string) ($this->getData(self::STATUS) ?: self::STATUS_PROCESSED);
    }

    public function setStatus(string $status): EventRegistrationInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getCreatedAt(): ?string
    {
        $value = $this->getData(self::CREATED_AT);
        return $value === null ? null : (string) $value;
    }
}
