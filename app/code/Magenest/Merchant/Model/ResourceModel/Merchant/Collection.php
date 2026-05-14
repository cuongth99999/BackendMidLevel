<?php
/**
 * app/code/Magenest/Merchant/Model/ResourceModel/Merchant/Collection.php
 *
 * EAV collection - reads from the entity table and joins value tables on demand
 * (via addAttributeToSelect / addAttributeToFilter).
 */
declare(strict_types=1);

namespace Magenest\Merchant\Model\ResourceModel\Merchant;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /** @var string */
    protected $_idFieldName = 'entity_id';

    /** @var string */
    protected $_eventPrefix = 'magenest_merchant_collection';

    /** @var string */
    protected $_eventObject = 'merchant_collection';

    protected function _construct(): void
    {
        $this->_init(
            \Magenest\Merchant\Model\Merchant::class,
            \Magenest\Merchant\Model\ResourceModel\Merchant::class
        );
    }
}
