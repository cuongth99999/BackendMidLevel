<?php
/**
 * app/code/Magenest/CustomerTraining/Controller/Adminhtml/Customer/NewAction.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\ForwardFactory;

class NewAction extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenest_CustomerTraining::customer_training_manage';

    public function __construct(
        Context $context,
        private readonly ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        return $this->resultForwardFactory->create()->forward('edit');
    }
}
