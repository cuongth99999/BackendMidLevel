<?php
declare(strict_types=1);

namespace Magenest\FullPageCache\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Http\Context;

class Gender extends Template
{
    public function __construct(
        Template\Context $context,
        private Context $httpContext,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    public function getText(): string
    {
        $v = (string)$this->httpContext->getValue('customer_gender');
        return ($v && $v !== 'guest') ? ('You are ' . $v) : '';
    }
}
