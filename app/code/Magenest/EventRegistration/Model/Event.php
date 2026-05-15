<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model;

use Magenest\EventRegistration\Api\Data\EventInterface;
use Magenest\EventRegistration\Model\ResourceModel\Event as EventResource;
use Magento\Framework\Model\AbstractModel;

class Event extends AbstractModel implements EventInterface
{
    protected $_eventPrefix = 'magenest_event';

    protected function _construct(): void
    {
        $this->_init(EventResource::class);
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

    public function getName(): ?string
    {
        $value = $this->getData(self::NAME);
        return $value === null ? null : (string) $value;
    }

    public function setName(string $name): EventInterface
    {
        return $this->setData(self::NAME, $name);
    }

    public function getDaysBeforeEvent(): int
    {
        return (int) $this->getData(self::DAYS_BEFORE_EVENT);
    }

    public function setDaysBeforeEvent(int $days): EventInterface
    {
        return $this->setData(self::DAYS_BEFORE_EVENT, $days);
    }

    public function getEventDate(): ?string
    {
        $value = $this->getData(self::EVENT_DATE);
        return $value === null ? null : (string) $value;
    }

    public function setEventDate(string $eventDate): EventInterface
    {
        return $this->setData(self::EVENT_DATE, $eventDate);
    }

    public function getSortOrder(): int
    {
        return (int) $this->getData(self::SORT_ORDER);
    }

    public function setSortOrder(int $sortOrder): EventInterface
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    public function getCreatedAt(): ?string
    {
        $value = $this->getData(self::CREATED_AT);
        return $value === null ? null : (string) $value;
    }

    public function getUpdatedAt(): ?string
    {
        $value = $this->getData(self::UPDATED_AT);
        return $value === null ? null : (string) $value;
    }
}
