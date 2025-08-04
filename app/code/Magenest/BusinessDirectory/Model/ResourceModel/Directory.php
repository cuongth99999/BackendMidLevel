<?php
declare(strict_types=1);

namespace Magenest\BusinessDirectory\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\App\Cache\TypeListInterface;

class Directory extends AbstractDb
{
    /**
     * @var TypeListInterface
     */
    private TypeListInterface $cacheTypeList;

    /**
     * @param Context $context
     * @param TypeListInterface $cacheTypeList
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        TypeListInterface $cacheTypeList,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('business_directory', 'entity_id');
    }

    /**
     * After save callback
     *
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        // Clear config cache when data is saved
        $this->cacheTypeList->cleanType('config');
        return parent::_afterSave($object);
    }

    /**
     * After delete callback
     *
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        // Clear config cache when data is deleted
        $this->cacheTypeList->cleanType('config');
        return parent::_afterDelete($object);
    }
}