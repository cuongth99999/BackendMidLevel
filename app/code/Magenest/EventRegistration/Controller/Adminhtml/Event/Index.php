<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Controller\Adminhtml\Event;

use Magenest\EventRegistration\Api\Data\EventInterface;
use Magenest\EventRegistration\Controller\Adminhtml\Event;
use Magenest\EventRegistration\Model\ResourceModel\Event\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Index extends Event
{
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        private readonly CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory);
    }

    public function execute(): ResultInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->setOrder(EventInterface::SORT_ORDER, 'ASC')
            ->setOrder(EventInterface::ENTITY_ID, 'ASC')
            ->setPageSize(1)
            ->setCurPage(1);

        $first = $collection->getFirstItem();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($first->getEntityId()) {
            return $resultRedirect->setPath('*/*/edit', ['id' => (int) $first->getEntityId()]);
        }
        return $resultRedirect->setPath('*/*/new');
    }
}
