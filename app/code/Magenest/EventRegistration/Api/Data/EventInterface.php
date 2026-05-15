<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Api\Data;

interface EventInterface
{
    public const ENTITY_ID         = 'entity_id';
    public const NAME              = 'name';
    public const DAYS_BEFORE_EVENT = 'days_before_event';
    public const EVENT_DATE        = 'event_date';
    public const SORT_ORDER        = 'sort_order';
    public const CREATED_AT        = 'created_at';
    public const UPDATED_AT        = 'updated_at';

    public function getEntityId(): ?int;

    /**
     * @param int|string $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    public function getName(): ?string;

    public function setName(string $name): self;

    public function getDaysBeforeEvent(): int;

    public function setDaysBeforeEvent(int $days): self;

    public function getEventDate(): ?string;

    public function setEventDate(string $eventDate): self;

    public function getSortOrder(): int;

    public function setSortOrder(int $sortOrder): self;

    public function getCreatedAt(): ?string;

    public function getUpdatedAt(): ?string;
}
