<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Event extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('magenest_event', 'entity_id');
    }
}
