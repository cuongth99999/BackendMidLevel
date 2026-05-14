<?php
/**
 * app/code/Magenest/Merchant/Controller/Adminhtml/Merchant/Edit.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Controller\Adminhtml\Merchant;

use Magenest\Merchant\Api\MerchantRepositoryInterface;
use Magenest\Merchant\Controller\Adminhtml\Merchant;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Merchant implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory,
        private readonly Registry $registry,
        private readonly MerchantRepositoryInterface $merchantRepository
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $id = (int) $this->getRequest()->getParam('entity_id');

        if ($id) {
            try {
                $merchant = $this->merchantRepository->getById($id);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This merchant no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $redirect */
                $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $redirect->setPath('*/*/index');
            }
        } else {
            $merchant = null;
        }

        $this->registry->register('magenest_merchant_current', $merchant);

        /** @var \Magento\Backend\Model\View\Result\Page $page */
        $page = $this->resultPageFactory->create();
        $page->setActiveMenu(self::ADMIN_RESOURCE);
        $page->getConfig()->getTitle()->prepend(
            $merchant && $merchant->getId()
                ? __('Edit Merchant "%1"', $merchant->getMerchantCode() ?: $merchant->getId())
                : __('New Merchant')
        );
        return $page;
    }
}
