<?php
/**
 * app/code/Magenest/BookingSchedule/Model/ResourceModel/BookingSchedule/Collection.php
 */
declare(strict_types=1);

namespace Magenest\BookingSchedule\Model\ResourceModel\BookingSchedule;

use Magenest\BookingSchedule\Model\BookingSchedule as BookingScheduleModel;
use Magenest\BookingSchedule\Model\ResourceModel\BookingSchedule as BookingScheduleResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(BookingScheduleModel::class, BookingScheduleResource::class);
    }
}
