<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model\Queue;

use Magenest\EventRegistration\Api\Data\MassRegisterRequestInterfaceFactory;
use Magento\Framework\MessageQueue\PublisherInterface;

class MassRegisterPublisher
{
    public const TOPIC      = 'magenest.event.register';
    public const BATCH_SIZE = 1000;

    public function __construct(
        private readonly PublisherInterface $publisher,
        private readonly MassRegisterRequestInterfaceFactory $requestFactory
    ) {
    }

    /**
     * Split $customerIds into ≤BATCH_SIZE chunks and publish one message per chunk.
     *
     * @return int Number of batches enqueued.
     */
    public function publish(int $eventId, int $scheduleId, ?string $note, array $customerIds): int
    {
        $clean = array_values(array_unique(array_filter(array_map('intval', $customerIds))));
        if (!$clean) {
            return 0;
        }

        $chunks = array_chunk($clean, self::BATCH_SIZE);
        foreach ($chunks as $chunk) {
            $request = $this->requestFactory->create();
            $request->setEventId($eventId);
            $request->setScheduleId($scheduleId);
            $request->setNote($note);
            $request->setCustomerIds($chunk);
            $this->publisher->publish(self::TOPIC, $request);
        }
        return count($chunks);
    }
}
