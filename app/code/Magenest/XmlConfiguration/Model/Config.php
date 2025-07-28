<?php

namespace Magenest\XmlConfiguration\Model;

/**
 * Class Config
 */
class Config
{
    /**
     * @var \Magento\Framework\Config\DataInterface
     */
    protected $_dataStorage;

    /**
     * @param \Magento\Framework\Config\DataInterface $dataStorage
     */
    public function __construct(\Magento\Framework\Config\DataInterface $dataStorage)
    {
        $this->_dataStorage = $dataStorage;
    }

    /**
     * Get renderer configuration data
     *
     * @return array
     */
    public function getResources()
    {
        return $this->_dataStorage->get();
    }
}
