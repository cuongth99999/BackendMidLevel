<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Controller\Adminhtml\Register;

use Magenest\EventRegistration\Api\Data\EventScheduleInterface;
use Magenest\EventRegistration\Api\EventScheduleRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class EventTimes extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenest_EventRegistration::event_register';

    public function __construct(
        Context $context,
        private readonly EventScheduleRepositoryInterface $scheduleRepository,
        private readonly JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $eventId = (int) $this->getRequest()->getParam('event_id');
        $result  = $this->jsonFactory->create();

        if ($eventId <= 0) {
            return $result->setData(['schedules' => []]);
        }

        $schedules = [];
        foreach ($this->scheduleRepository->getByEventId($eventId) as $row) {
            /** @var EventScheduleInterface $row */
            $schedules[] = [
                'id'              => (int) $row->getEntityId(),
                'day_of_week'     => (string) $row->getDayOfWeek(),
                'schedule_date'   => (string) $row->getScheduleDate(),
                'event_time'      => $this->formatTime($row->getEventTime()),
                'event_time_raw'  => (string) ($row->getEventTime() ?? ''),
                'details_message' => (string) ($row->getDetailsMessage() ?? ''),
                'label'           => $this->buildLabel($row),
            ];
        }

        return $result->setData(['schedules' => $schedules]);
    }

    private function buildLabel(EventScheduleInterface $row): string
    {
        $parts = array_filter([
            $row->getScheduleDate(),
            $row->getDayOfWeek(),
            $this->formatTime($row->getEventTime()),
        ]);
        return implode(' — ', $parts);
    }

    private function formatTime(?string $time): string
    {
        if (!$time) {
            return '';
        }
        $ts = strtotime((string) $time);
        return $ts === false ? (string) $time : date('g:i A', $ts);
    }
}
