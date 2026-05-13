<?php
/**
 * app/code/Magenest/BookingSchedule/Model/BookingScheduleRepository.php
 */
declare(strict_types=1);

namespace Magenest\BookingSchedule\Model;

use Magenest\BookingSchedule\Api\BookingScheduleRepositoryInterface;
use Magenest\BookingSchedule\Api\Data\BookingScheduleInterface;
use Magenest\BookingSchedule\Model\ResourceModel\BookingSchedule as BookingScheduleResource;
use Magenest\BookingSchedule\Model\ResourceModel\BookingSchedule\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class BookingScheduleRepository implements BookingScheduleRepositoryInterface
{
    public function __construct(
        private readonly BookingScheduleResource $resource,
        private readonly BookingScheduleFactory $modelFactory,
        private readonly CollectionFactory $collectionFactory
    ) {
    }

    public function save(BookingScheduleInterface $schedule): BookingScheduleInterface
    {
        try {
            /** @var BookingSchedule $schedule */
            $this->resource->save($schedule);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(__('Could not save booking slot: %1', $e->getMessage()), $e);
        }
        return $schedule;
    }

    public function getById(int $entityId): BookingScheduleInterface
    {
        /** @var BookingSchedule $model */
        $model = $this->modelFactory->create();
        $this->resource->load($model, $entityId);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Booking slot with id "%1" does not exist.', $entityId));
        }
        return $model;
    }

    public function getBySlot(string $date, string $time): ?BookingScheduleInterface
    {
        /** @var \Magenest\BookingSchedule\Model\ResourceModel\BookingSchedule\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(BookingScheduleInterface::SCHEDULE_DATE, $date)
            ->addFieldToFilter(BookingScheduleInterface::SCHEDULE_TIME, $time)
            ->setPageSize(1);

        $item = $collection->getFirstItem();
        return $item->getId() ? $item : null;
    }

    public function getRange(string $from, string $to): array
    {
        /** @var \Magenest\BookingSchedule\Model\ResourceModel\BookingSchedule\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            BookingScheduleInterface::SCHEDULE_DATE,
            ['from' => $from, 'to' => $to, 'date' => true]
        );

        $result = [];
        /** @var BookingSchedule $item */
        foreach ($collection as $item) {
            $key = $item->getScheduleDate() . '|' . $item->getScheduleTime();
            $result[$key] = $item;
        }
        return $result;
    }
}
