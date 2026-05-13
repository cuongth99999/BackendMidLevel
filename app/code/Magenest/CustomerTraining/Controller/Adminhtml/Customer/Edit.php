<?php
/**
 * app/code/Magenest/CustomerTraining/Controller/Adminhtml/Customer/Edit.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Controller\Adminhtml\Customer;

use Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenest_CustomerTraining::customer_training_manage';
    public const REGISTRY_KEY = 'magenest_customer_training_entity';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory,
        private readonly Registry $coreRegistry,
        private readonly CustomerTrainingRepositoryInterface $repository
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $entityId = (int) $this->getRequest()->getParam('entity_id');
        $model = null;

        if ($entityId) {
            try {
                $model = $this->repository->getById($entityId);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This customer no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/index');
            }
        }

        if ($model !== null) {
            $this->coreRegistry->register(self::REGISTRY_KEY, $model);
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_CustomerTraining::customer_list');
        $resultPage->addBreadcrumb(__('Customer Training'), __('Customer Training'));
        $resultPage->addBreadcrumb(__('Manage Customers'), __('Manage Customers'));
        $resultPage->getConfig()->getTitle()->prepend(
            $entityId ? __('Edit Customer #%1', $entityId) : __('New Customer')
        );
        return $resultPage;
    }
}
