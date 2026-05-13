<?php
/**
 * app/code/Magenest/BookingSchedule/Controller/Adminhtml/Schedule/Index.php
 */
declare(strict_types=1);

namespace Magenest\BookingSchedule\Controller\Adminhtml\Schedule;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenest_BookingSchedule::schedule';

    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Magenest_BookingSchedule::schedule');
        $page->getConfig()->getTitle()->prepend(__('Booking Schedule'));
        return $page;
    }
}
