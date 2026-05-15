<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Api\Data;

interface EventScheduleInterface
{
    public const ENTITY_ID       = 'entity_id';
    public const EVENT_ID        = 'event_id';
    public const DAY_OF_WEEK     = 'day_of_week';
    public const SCHEDULE_DATE   = 'schedule_date';
    public const DETAILS_MESSAGE = 'details_message';
    public const EVENT_TIME      = 'event_time';
    public const POSITION        = 'position';

    public function getEntityId(): ?int;

    /**
     * @param int|string $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    public function getEventId(): int;

    public function setEventId(int $eventId): self;

    public function getDayOfWeek(): ?string;

    public function setDayOfWeek(string $dayOfWeek): self;

    public function getScheduleDate(): ?string;

    public function setScheduleDate(string $scheduleDate): self;

    public function getDetailsMessage(): ?string;

    public function setDetailsMessage(?string $detailsMessage): self;

    public function getEventTime(): ?string;

    public function setEventTime(?string $eventTime): self;

    public function getPosition(): int;

    public function setPosition(int $position): self;
}
