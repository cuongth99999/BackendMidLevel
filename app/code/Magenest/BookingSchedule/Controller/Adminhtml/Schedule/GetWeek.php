<?php
/**
 * app/code/Magenest/BookingSchedule/Controller/Adminhtml/Schedule/GetWeek.php
 */
declare(strict_types=1);

namespace Magenest\BookingSchedule\Controller\Adminhtml\Schedule;

use Magenest\BookingSchedule\Api\BookingScheduleRepositoryInterface;
use Magenest\BookingSchedule\Api\Data\BookingScheduleInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

class GetWeek extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenest_BookingSchedule::schedule';

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly BookingScheduleRepositoryInterface $repository,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();
        $weekStart = (string) $this->getRequest()->getParam('week_start', '');

        try {
            $start = $this->normalizeMonday($weekStart);
            $end   = (clone $start)->modify('+6 days');

            $slots = $this->repository->getRange(
                $start->format('Y-m-d'),
                $end->format('Y-m-d')
            );

            $payload = [];
            /** @var BookingScheduleInterface $slot */
            foreach ($slots as $key => $slot) {
                $payload[$key] = [
                    'stock'       => $slot->getStock(),
                    'used'        => $slot->getUsed(),
                    'reservation' => $slot->getReservation(),
                ];
            }

            return $result->setData([
                'success'    => true,
                'week_start' => $start->format('Y-m-d'),
                'slots'      => (object) $payload,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('[BookingSchedule] getWeek failed: ' . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => __('Unable to load the requested week.')->render(),
            ]);
        }
    }

    /**
     * Snap an arbitrary "YYYY-MM-DD" (or empty) to the Monday of that ISO week.
     */
    private function normalizeMonday(string $date): \DateTimeImmutable
    {
        try {
            $dt = $date !== '' ? new \DateTimeImmutable($date) : new \DateTimeImmutable('today');
        } catch (\Throwable) {
            $dt = new \DateTimeImmutable('today');
        }
        // ISO-8601: Monday = 1
        $dayOfWeek = (int) $dt->format('N');
        if ($dayOfWeek > 1) {
            $dt = $dt->modify('-' . ($dayOfWeek - 1) . ' days');
        }
        return $dt->setTime(0, 0);
    }
}
