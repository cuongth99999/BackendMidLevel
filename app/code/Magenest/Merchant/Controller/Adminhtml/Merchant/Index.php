<?php
/**
 * app/code/Magenest/Merchant/Controller/Adminhtml/Merchant/Index.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Controller\Adminhtml\Merchant;

use Magenest\Merchant\Controller\Adminhtml\Merchant;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Merchant implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->getConfig()->getTitle()->prepend(__('Merchants'));
        return $resultPage;
    }
}
