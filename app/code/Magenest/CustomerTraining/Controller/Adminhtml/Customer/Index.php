<?php
/**
 * app/code/Magenest/CustomerTraining/Controller/Adminhtml/Customer/Index.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenest_CustomerTraining::customer_training_manage';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_CustomerTraining::customer_list');
        $resultPage->addBreadcrumb(__('Customer Training'), __('Customer Training'));
        $resultPage->addBreadcrumb(__('Manage Customers'), __('Manage Customers'));
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Training'));
        return $resultPage;
    }
}
