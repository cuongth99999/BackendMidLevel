<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model\ResourceModel\EventSchedule;

use Magenest\EventRegistration\Model\EventSchedule as EventScheduleModel;
use Magenest\EventRegistration\Model\ResourceModel\EventSchedule as EventScheduleResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(EventScheduleModel::class, EventScheduleResource::class);
    }
}
