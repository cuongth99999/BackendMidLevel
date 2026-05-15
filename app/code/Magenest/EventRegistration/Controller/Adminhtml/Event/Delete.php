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

class Delete extends Event
{
    public const ADMIN_RESOURCE = 'Magenest_EventRegistration::event_delete';

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
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int) $this->getRequest()->getParam('id');
        if (!$id) {
            $this->messageManager->addErrorMessage(__('Event id is required.'));
            return $resultRedirect->setPath('*/*/index');
        }

        try {
            $this->eventRepository->deleteById($id);
            $this->messageManager->addSuccessMessage(__('Event deleted.'));
            return $resultRedirect->setPath('*/*/index');
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This event no longer exists.'));
        } catch (\Throwable $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to delete event.'));
        }

        return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
    }
}
