<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model\Queue;

use Magenest\EventRegistration\Api\Data\MassRegisterRequestInterface;

class MassRegisterRequest implements MassRegisterRequestInterface
{
    private int $eventId = 0;
    private int $scheduleId = 0;
    private ?string $note = null;
    /** @var int[] */
    private array $customerIds = [];

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function setEventId(int $eventId): void
    {
        $this->eventId = $eventId;
    }

    public function getScheduleId(): int
    {
        return $this->scheduleId;
    }

    public function setScheduleId(int $scheduleId): void
    {
        $this->scheduleId = $scheduleId;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    /**
     * @return int[]
     */
    public function getCustomerIds(): array
    {
        return $this->customerIds;
    }

    /**
     * @param int[] $customerIds
     */
    public function setCustomerIds(array $customerIds): void
    {
        $this->customerIds = array_values(array_map('intval', $customerIds));
    }
}
