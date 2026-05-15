<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Api\Data;

interface EventRegistrationInterface
{
    public const ENTITY_ID   = 'entity_id';
    public const CUSTOMER_ID = 'customer_id';
    public const EVENT_ID    = 'event_id';
    public const SCHEDULE_ID = 'schedule_id';
    public const NOTE        = 'note';
    public const STATUS      = 'status';
    public const CREATED_AT  = 'created_at';

    public const STATUS_PROCESSED = 'processed';
    public const STATUS_FAILED    = 'failed';

    public function getEntityId(): ?int;

    /**
     * @param int|string $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    public function getCustomerId(): int;

    public function setCustomerId(int $customerId): self;

    public function getEventId(): int;

    public function setEventId(int $eventId): self;

    public function getScheduleId(): int;

    public function setScheduleId(int $scheduleId): self;

    public function getNote(): ?string;

    public function setNote(?string $note): self;

    public function getStatus(): string;

    public function setStatus(string $status): self;

    public function getCreatedAt(): ?string;
}
