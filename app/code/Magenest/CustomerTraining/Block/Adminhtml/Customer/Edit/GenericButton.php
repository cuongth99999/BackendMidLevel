<?php
/**
 * app/code/Magenest/CustomerTraining/Block/Adminhtml/Customer/Edit/GenericButton.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Block\Adminhtml\Customer\Edit;

use Magento\Backend\Block\Widget\Context;

abstract class GenericButton
{
    public function __construct(
        protected readonly Context $context
    ) {
    }

    public function getEntityId(): ?int
    {
        $id = $this->context->getRequest()->getParam('entity_id');
        return $id !== null ? (int) $id : null;
    }

    protected function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
