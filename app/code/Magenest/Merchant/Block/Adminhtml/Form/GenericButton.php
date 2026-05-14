<?php
/**
 * app/code/Magenest/Merchant/Block/Adminhtml/Form/GenericButton.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Block\Adminhtml\Form;

use Magento\Backend\Block\Widget\Context;

class GenericButton
{
    public function __construct(protected Context $context)
    {
    }

    public function getMerchantId(): ?int
    {
        $id = $this->context->getRequest()->getParam('entity_id');
        return $id ? (int) $id : null;
    }

    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
