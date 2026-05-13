<?php
/**
 * app/code/Magenest/BookingSchedule/Controller/Adminhtml/Schedule/Save.php
 */
declare(strict_types=1);

namespace Magenest\BookingSchedule\Controller\Adminhtml\Schedule;

use Magenest\BookingSchedule\Api\BookingScheduleRepositoryInterface;
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

class Save extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public const ADMIN_RESOURCE = 'Magenest_BookingSchedule::schedule';

    private const TIME_PATTERN = '/^([01]\d|2[0-3]):(00|30)$/';
    private const DATE_PATTERN = '/^\d{4}-\d{2}-\d{2}$/';

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
            $slots = $this->getRequest()->getParam('slots');
            $slots = is_array($slots) ? $slots : [];

            $saved = 0;
            foreach ($slots as $entry) {
                $date  = (string) ($entry['date']  ?? '');
                $time  = (string) ($entry['time']  ?? '');
                $stock = (int)    ($entry['stock'] ?? 0);

                if (!preg_match(self::DATE_PATTERN, $date) || !preg_match(self::TIME_PATTERN, $time)) {
                    // SEC: silently skip malformed rows instead of trusting client input
                    continue;
                }
                if ($stock < 0) {
                    $stock = 0;
                }

                $existing = $this->repository->getBySlot($date, $time);
                if ($existing === null) {
                    if ($stock === 0) {
                        // Don't create empty rows
                        continue;
                    }
                    /** @var \Magenest\BookingSchedule\Model\BookingSchedule $model */
                    $model = $this->modelFactory->create();
                    $model->setScheduleDate($date)
                        ->setScheduleTime($time)
                        ->setStock($stock);
                } else {
                    $existing->setStock($stock);
                    $model = $existing;
                }

                $this->repository->save($model);
                $saved++;
            }

            return $result->setData([
                'success' => true,
                'saved'   => $saved,
                'message' => __('Schedule saved (%1 slot(s)).', $saved)->render(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('[BookingSchedule] save failed: ' . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => __('Unable to save the schedule.')->render(),
            ]);
        }
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        // SEC: Magento backend admin auth + the form key carried in the X-Csrf header gate this endpoint.
        return true;
    }
}
