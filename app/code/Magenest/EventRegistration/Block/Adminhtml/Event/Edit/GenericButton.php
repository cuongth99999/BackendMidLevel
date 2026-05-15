<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Block\Adminhtml\Event\Edit;

use Magento\Backend\Block\Widget\Context;

abstract class GenericButton
{
    public function __construct(
        protected readonly Context $context
    ) {
    }

    public function getEventId(): ?int
    {
        $id = $this->context->getRequest()->getParam('id');
        return $id ? (int) $id : null;
    }

    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
