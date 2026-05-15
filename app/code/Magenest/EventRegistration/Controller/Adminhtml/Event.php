<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

abstract class Event extends Action
{
    public const ADMIN_RESOURCE = 'Magenest_EventRegistration::event';

    public function __construct(
        Context $context,
        protected readonly Registry $coreRegistry,
        protected readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magenest_EventRegistration::event')
            ->addBreadcrumb(__('Event Registration'), __('Event Registration'))
            ->addBreadcrumb(__('Manage Events'), __('Manage Events'));
        return $resultPage;
    }
}
