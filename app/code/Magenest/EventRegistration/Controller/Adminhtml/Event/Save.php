<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Controller\Adminhtml\Event;

use Magenest\EventRegistration\Api\Data\EventInterface;
use Magenest\EventRegistration\Api\Data\EventScheduleInterface;
use Magenest\EventRegistration\Api\EventRepositoryInterface;
use Magenest\EventRegistration\Api\EventScheduleRepositoryInterface;
use Magenest\EventRegistration\Controller\Adminhtml\Event;
use Magenest\EventRegistration\Model\EventFactory;
use Magenest\EventRegistration\Model\EventScheduleFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Save extends Event implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenest_EventRegistration::event_save';

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly EventScheduleRepositoryInterface $scheduleRepository,
        private readonly EventFactory $eventFactory,
        private readonly EventScheduleFactory $scheduleFactory
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory);
    }

    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = (array) $this->getRequest()->getPostValue();
        if (empty($data)) {
            return $resultRedirect->setPath('*/*/index');
        }

        $id = isset($data['entity_id']) ? (int) $data['entity_id'] : 0;

        try {
            $event = $id
                ? $this->eventRepository->getById($id)
                : $this->eventFactory->create();

            $name = trim((string) ($data['name'] ?? ''));
            if ($name === '') {
                throw new LocalizedException(__('Event Name is required.'));
            }
            $days = (int) ($data['days_before_event'] ?? 0);
            if ($days < 1) {
                throw new LocalizedException(__('Days before event must be at least 1.'));
            }
            $eventDate = trim((string) ($data['event_date'] ?? ''));
            if ($eventDate === '') {
                throw new LocalizedException(__('Event Date is required.'));
            }
            $eventDate = $this->normalizeDate($eventDate);

            $event->setName($name);
            $event->setDaysBeforeEvent($days);
            $event->setEventDate($eventDate);
            $event->setSortOrder((int) ($data['sort_order'] ?? 0));

            $event = $this->eventRepository->save($event);
            $eventId = (int) $event->getEntityId();

            // dynamicRows POSTs records under schedule[schedule][X][...]; older
            // wiring may also leave a flat schedule[X][...] array — accept both.
            $scheduleData = (array) ($data['schedule'] ?? []);
            $userRows     = isset($scheduleData['schedule']) && is_array($scheduleData['schedule'])
                ? $scheduleData['schedule']
                : array_filter($scheduleData, 'is_array');

            $this->saveSchedule($eventId, $eventDate, $days, $userRows);

            $this->messageManager->addSuccessMessage(__('Event saved.'));

            if ($this->getRequest()->getParam('back') === 'edit') {
                return $resultRedirect->setPath('*/*/edit', ['id' => $eventId]);
            }
            return $resultRedirect->setPath('*/*/edit', ['id' => $eventId]);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Throwable $e) {
            $this->messageManager->addExceptionMessage($e, __('Unexpected error while saving the event.'));
        }

        $params = ['id' => $id ?: null];
        return $resultRedirect->setPath($id ? '*/*/edit' : '*/*/new', array_filter($params));
    }

    /**
     * Persist N schedule rows (N = days_before_event). day_of_week / schedule_date
     * are derived server-side from $eventDate so the form does not have to submit
     * them; only event_time / details_message come from the user.
     */
    private function saveSchedule(int $eventId, string $eventDate, int $days, array $rows): void
    {
        $this->scheduleRepository->deleteByEventId($eventId);
        if ($days < 1) {
            return;
        }

        // Index user-submitted rows by position so we can match them by row order.
        $byPosition = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $pos = isset($row['position']) ? (int) $row['position'] : null;
            if ($pos === null || !array_key_exists($pos, $byPosition)) {
                $byPosition[$pos ?? count($byPosition)] = $row;
            }
        }

        $endTs = strtotime($eventDate);
        if ($endTs === false) {
            return;
        }

        for ($i = 0; $i < $days; $i++) {
            $rowDateTs = strtotime('-' . ($days - 1 - $i) . ' days', $endTs);
            $rowDate   = date('Y-m-d', $rowDateTs);
            $rowDay    = date('l', $rowDateTs);

            $userRow = $byPosition[$i] ?? [];

            $schedule = $this->scheduleFactory->create();
            $schedule->setEventId($eventId);
            $schedule->setDayOfWeek($rowDay);
            $schedule->setScheduleDate($rowDate);
            $schedule->setDetailsMessage(
                isset($userRow['details_message']) ? (string) $userRow['details_message'] : null
            );
            $schedule->setEventTime($this->normalizeTime((string) ($userRow['event_time'] ?? '')));
            $schedule->setPosition($i);
            $this->scheduleRepository->save($schedule);
        }
    }

    private function normalizeDate(string $value): string
    {
        $ts = strtotime($value);
        if ($ts === false) {
            throw new LocalizedException(__('Invalid date value: %1', $value));
        }
        return date('Y-m-d', $ts);
    }

    private function normalizeTime(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $ts = strtotime($value);
        if ($ts === false) {
            return null;
        }
        return date('H:i:s', $ts);
    }
}
