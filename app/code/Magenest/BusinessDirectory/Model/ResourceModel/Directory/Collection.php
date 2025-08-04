<?php
declare(strict_types=1);

namespace Magenest\BusinessDirectory\Model\ResourceModel\Directory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magenest\BusinessDirectory\Model\Directory as Model;
use Magenest\BusinessDirectory\Model\ResourceModel\Directory as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'magenest_business_directory_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getItems() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }
        return $identities;
    }
}