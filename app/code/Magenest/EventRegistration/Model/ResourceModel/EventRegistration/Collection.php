<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model\ResourceModel\EventRegistration;

use Magenest\EventRegistration\Model\EventRegistration as RegistrationModel;
use Magenest\EventRegistration\Model\ResourceModel\EventRegistration as RegistrationResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(RegistrationModel::class, RegistrationResource::class);
    }
}
