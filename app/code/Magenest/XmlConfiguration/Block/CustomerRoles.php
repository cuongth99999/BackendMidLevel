<?php

namespace Magenest\XmlConfiguration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\ResourceConnection;
use Magenest\XmlConfiguration\Model\Config;

class CustomerRoles extends Template
{
    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    )
    {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    public function getRoles()
    {
        return $this->config->getResources();
    }
}
