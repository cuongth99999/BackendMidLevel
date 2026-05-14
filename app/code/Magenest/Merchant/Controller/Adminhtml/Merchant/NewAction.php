<?php
/**
 * app/code/Magenest/Merchant/Controller/Adminhtml/Merchant/NewAction.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Controller\Adminhtml\Merchant;

use Magenest\Merchant\Controller\Adminhtml\Merchant;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class NewAction extends Merchant implements HttpGetActionInterface
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $forward */
        $forward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        return $forward->forward('edit');
    }
}
