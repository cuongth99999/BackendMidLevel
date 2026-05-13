<?php
/**
 * app/code/Magenest/CustomerTraining/Model/ResourceModel/CustomerTraining/Collection.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Model\ResourceModel\CustomerTraining;

use Magenest\CustomerTraining\Model\CustomerTraining as CustomerTrainingModel;
use Magenest\CustomerTraining\Model\ResourceModel\CustomerTraining as CustomerTrainingResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(CustomerTrainingModel::class, CustomerTrainingResource::class);
    }
}
