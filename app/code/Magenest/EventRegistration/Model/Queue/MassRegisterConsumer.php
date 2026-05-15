<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model\Queue;

use Magenest\EventRegistration\Api\Data\EventRegistrationInterface;
use Magenest\EventRegistration\Api\Data\MassRegisterRequestInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class MassRegisterConsumer
{
    private const TABLE = 'magenest_event_registration';

    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Bulk insert one batch of (customer_id, event_id, schedule_id, note) rows.
     * `INSERT ... ON DUPLICATE KEY UPDATE` lets us safely re-run the same payload
     * without violating the unique (customer_id, schedule_id) constraint.
     */
    public function process(MassRegisterRequestInterface $request): void
    {
        $eventId     = $request->getEventId();
        $scheduleId  = $request->getScheduleId();
        $note        = $request->getNote();
        $customerIds = $request->getCustomerIds();

        if (!$eventId || !$scheduleId || !$customerIds) {
            $this->logger->warning('[MassRegisterConsumer] Skipped: empty payload.');
            return;
        }

        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName(self::TABLE);
        $now        = date('Y-m-d H:i:s');

        $rows = [];
        foreach ($customerIds as $cid) {
            $cid = (int) $cid;
            if ($cid <= 0) {
                continue;
            }
            $rows[] = [
                EventRegistrationInterface::CUSTOMER_ID => $cid,
                EventRegistrationInterface::EVENT_ID    => $eventId,
                EventRegistrationInterface::SCHEDULE_ID => $scheduleId,
                EventRegistrationInterface::NOTE        => $note,
                EventRegistrationInterface::STATUS      => EventRegistrationInterface::STATUS_PROCESSED,
                EventRegistrationInterface::CREATED_AT  => $now,
            ];
        }

        if (!$rows) {
            return;
        }

        try {
            $connection->insertOnDuplicate(
                $table,
                $rows,
                [EventRegistrationInterface::NOTE, EventRegistrationInterface::STATUS]
            );
            $this->logger->info(sprintf(
                '[MassRegisterConsumer] Registered %d customers to event=%d schedule=%d.',
                count($rows),
                $eventId,
                $scheduleId
            ));
        } catch (\Throwable $e) {
            $this->logger->error('[MassRegisterConsumer] Insert failed: ' . $e->getMessage(), [
                'exception'   => $e,
                'event_id'    => $eventId,
                'schedule_id' => $scheduleId,
                'batch_size'  => count($rows),
            ]);
            throw $e;
        }
    }
}
