<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Controller\Adminhtml\Event;

use Magenest\EventRegistration\Controller\Adminhtml\Event;
use Magento\Framework\Controller\ResultInterface;

class NewAction extends Event
{
    public function execute(): ResultInterface
    {
        $resultForward = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD);
        return $resultForward->forward('edit');
    }
}
