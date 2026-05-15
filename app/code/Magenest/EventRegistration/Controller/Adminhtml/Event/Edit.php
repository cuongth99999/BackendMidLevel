<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Controller\Adminhtml\Event;

use Magenest\EventRegistration\Api\EventRepositoryInterface;
use Magenest\EventRegistration\Controller\Adminhtml\Event;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Event
{
    public const REGISTRY_KEY = 'magenest_eventregistration_current_event';

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        private readonly EventRepositoryInterface $eventRepository
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory);
    }

    public function execute(): ResultInterface
    {
        $id = (int) $this->getRequest()->getParam('id');
        $event = null;

        if ($id) {
            try {
                $event = $this->eventRepository->getById($id);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This event no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/index');
            }
            $this->coreRegistry->register(self::REGISTRY_KEY, $event);
        }

        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage);
        $title = $event && $event->getEntityId()
            ? __('Edit Event "%1"', $event->getName())
            : __('New Event');
        $resultPage->getConfig()->getTitle()->prepend(__('Events'));
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
}
