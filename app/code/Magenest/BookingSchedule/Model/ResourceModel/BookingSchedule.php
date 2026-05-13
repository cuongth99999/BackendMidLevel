<?php
/**
 * app/code/Magenest/BookingSchedule/Model/ResourceModel/BookingSchedule.php
 */
declare(strict_types=1);

namespace Magenest\BookingSchedule\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class BookingSchedule extends AbstractDb
{
    public const TABLE_NAME = 'magenest_booking_schedule';

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}
