<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Api\Data;

/**
 * Payload published to topic "magenest.event.register".
 * Each message represents one batch (≤1000) of customer IDs to register.
 */
interface MassRegisterRequestInterface
{
    /**
     * @return int
     */
    public function getEventId(): int;

    /**
     * @param int $eventId
     * @return void
     */
    public function setEventId(int $eventId): void;

    /**
     * @return int
     */
    public function getScheduleId(): int;

    /**
     * @param int $scheduleId
     * @return void
     */
    public function setScheduleId(int $scheduleId): void;

    /**
     * @return string|null
     */
    public function getNote(): ?string;

    /**
     * @param string|null $note
     * @return void
     */
    public function setNote(?string $note): void;

    /**
     * @return int[]
     */
    public function getCustomerIds(): array;

    /**
     * @param int[] $customerIds
     * @return void
     */
    public function setCustomerIds(array $customerIds): void;
}
