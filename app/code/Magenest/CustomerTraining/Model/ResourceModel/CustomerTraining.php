<?php
/**
 * app/code/Magenest/CustomerTraining/Model/ResourceModel/CustomerTraining.php
 *
 * NOTE: The `connectionName` argument is injected via etc/di.xml to point
 * this resource at the `well_trained` database (separate from Magento's
 * default DB).
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomerTraining extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('customer_training', 'entity_id');
    }
}
