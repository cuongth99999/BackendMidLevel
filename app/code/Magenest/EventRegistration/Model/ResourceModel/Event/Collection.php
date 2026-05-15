<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model\ResourceModel\Event;

use Magenest\EventRegistration\Model\Event as EventModel;
use Magenest\EventRegistration\Model\ResourceModel\Event as EventResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(EventModel::class, EventResource::class);
    }
}
