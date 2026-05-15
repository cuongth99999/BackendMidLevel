<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model\Event;

use Magenest\EventRegistration\Api\Data\EventInterface;
use Magenest\EventRegistration\Api\Data\EventScheduleInterface;
use Magenest\EventRegistration\Api\EventScheduleRepositoryInterface;
use Magenest\EventRegistration\Model\ResourceModel\Event\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    private ?array $loadedData = null;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        private readonly RequestInterface $request,
        private readonly EventScheduleRepositoryInterface $scheduleRepository,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        $id = (int) $this->request->getParam('id');

        if ($id) {
            /** @var EventInterface $event */
            foreach ($this->collection->getItems() as $event) {
                if ((int) $event->getEntityId() !== $id) {
                    continue;
                }
                $eventData = $event->getData();
                // dynamicRows links records at "<dataScope>.<index>", so when
                // name="schedule" and dataScope="schedule" the records live at
                // data.schedule.schedule.* — match that shape here so the form
                // can pre-populate the grid AND the POST stays consistent.
                $eventData['schedule'] = ['schedule' => $this->getScheduleRows((int) $event->getEntityId())];
                $this->loadedData[$event->getEntityId()] = $eventData;
            }
            return $this->loadedData;
        }

        // New event — provide a default skeleton so the form binds correctly.
        $this->loadedData[''] = [
            EventInterface::ENTITY_ID         => null,
            EventInterface::NAME              => '',
            EventInterface::DAYS_BEFORE_EVENT => 1,
            EventInterface::EVENT_DATE        => '',
            EventInterface::SORT_ORDER        => 0,
            'schedule'                        => ['schedule' => []],
        ];

        return $this->loadedData;
    }

    private function getScheduleRows(int $eventId): array
    {
        $rows = [];
        $position = 0;
        foreach ($this->scheduleRepository->getByEventId($eventId) as $row) {
            /** @var EventScheduleInterface $row */
            $rows[] = [
                'position'                            => $row->getPosition() ?: $position,
                EventScheduleInterface::DAY_OF_WEEK   => $row->getDayOfWeek(),
                EventScheduleInterface::SCHEDULE_DATE => $row->getScheduleDate(),
                EventScheduleInterface::EVENT_TIME    => $this->formatTimeForDisplay($row->getEventTime()),
                EventScheduleInterface::DETAILS_MESSAGE => $row->getDetailsMessage(),
            ];
            $position++;
        }
        return $rows;
    }

    private function formatTimeForDisplay(?string $time): string
    {
        if (!$time) {
            return '';
        }
        $ts = strtotime($time);
        if ($ts === false) {
            return (string) $time;
        }
        return date('g:i A', $ts);
    }
}
