<?php
/**
 * app/code/Magenest/BookingSchedule/Controller/Adminhtml/Schedule/CopyWeek.php
 */
declare(strict_types=1);

namespace Magenest\BookingSchedule\Controller\Adminhtml\Schedule;

use Magenest\BookingSchedule\Api\BookingScheduleRepositoryInterface;
use Magenest\BookingSchedule\Api\Data\BookingScheduleInterface;
use Magenest\BookingSchedule\Model\BookingScheduleFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

class CopyWeek extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public const ADMIN_RESOURCE = 'Magenest_BookingSchedule::schedule';

    private const MAX_COPIES = 52;

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly BookingScheduleRepositoryInterface $repository,
        private readonly BookingScheduleFactory $modelFactory,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();

        try {
            $sourceWeekStart = (string) $this->getRequest()->getParam('week_start', '');
            $copies          = (int) $this->getRequest()->getParam('copies', 0);

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sourceWeekStart) || $copies < 1) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Please provide a valid week and a positive copy count.')->render(),
                ]);
            }
            if ($copies > self::MAX_COPIES) {
                $copies = self::MAX_COPIES;
            }

            $start = new \DateTimeImmutable($sourceWeekStart);
            $end   = $start->modify('+6 days');

            $sourceSlots = $this->repository->getRange(
                $start->format('Y-m-d'),
                $end->format('Y-m-d')
            );

            if (empty($sourceSlots)) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Source week has no data to copy.')->render(),
                ]);
            }

            $copied = 0;
            for ($i = 1; $i <= $copies; $i++) {
                $offsetDays = 7 * $i;
                /** @var BookingScheduleInterface $slot */
                foreach ($sourceSlots as $slot) {
                    $targetDate = (new \DateTimeImmutable($slot->getScheduleDate()))
                        ->modify('+' . $offsetDays . ' days')
                        ->format('Y-m-d');

                    $existing = $this->repository->getBySlot($targetDate, $slot->getScheduleTime());
                    if ($existing === null) {
                        /** @var \Magenest\BookingSchedule\Model\BookingSchedule $model */
                        $model = $this->modelFactory->create();
                        $model->setScheduleDate($targetDate)
                            ->setScheduleTime($slot->getScheduleTime());
                    } else {
                        $model = $existing;
                    }
                    // Only stock is copied; reservation/used are slot-specific live counters.
                    $model->setStock($slot->getStock());
                    $this->repository->save($model);
                    $copied++;
                }
            }

            return $result->setData([
                'success' => true,
                'copied'  => $copied,
                'message' => __('Copied %1 slot record(s) across %2 week(s).', $copied, $copies)->render(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('[BookingSchedule] copyWeek failed: ' . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => __('Unable to copy the week.')->render(),
            ]);
        }
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
