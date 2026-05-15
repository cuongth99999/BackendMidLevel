<?php
/**
 * app/code/Magenest/Merchant/Controller/Adminhtml/Customer/MassAssignMerchant/Edit.php
 *
 * Receives the customer grid's massaction POST (namespace + selected[] /
 * excluded[] + filters), resolves the selection into a flat list of
 * customer IDs via Ui\MassAction\Filter, and renders the merchant
 * picker form.
 *
 * Why stash IDs in session rather than passing them through the form as
 * hidden inputs:
 *  - At 5k IDs the hidden array adds ~30-50KB to every request and HTML
 *    page. Session storage keeps the form lean.
 *  - The form POST goes to a different controller; session is the
 *    cleanest carrier across the redirect-ish round trip.
 *
 * We resolve IDs HERE (not in Save) because the massaction POST body is
 * the only place where `selected[]`/`filters` are available; the form
 * itself doesn't carry the grid selection forward.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Controller\Adminhtml\Customer\MassAssignMerchant;

use Magenest\Merchant\Controller\Adminhtml\Merchant as AbstractMerchant;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;

class Edit extends AbstractMerchant implements HttpPostActionInterface
{
    public const SESSION_KEY_CUSTOMER_IDS = 'magenest_merchant_mass_assign_customer_ids';

    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $customerCollectionFactory,
        private readonly PageFactory $resultPageFactory,
        private readonly Session $adminSession
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        try {
            // Filter::getCollection() reads namespace + selected[]/excluded[]
            // + active filters from the request and applies them to the
            // collection passed in. getAllIds() then returns just the IDs.
            $collection = $this->filter->getCollection($this->customerCollectionFactory->create());
            $customerIds = array_map('intval', $collection->getAllIds());
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(
                __('Could not resolve customer selection: %1', $e->getMessage())
            );
            return $this->resultRedirectFactory->create()->setPath('customer/index/index');
        }

        if (!$customerIds) {
            $this->messageManager->addErrorMessage(__('Please select at least one customer.'));
            return $this->resultRedirectFactory->create()->setPath('customer/index/index');
        }

        $this->adminSession->setData(self::SESSION_KEY_CUSTOMER_IDS, $customerIds);

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Customer::customer_manage');
        $resultPage->getConfig()->getTitle()->prepend(
            (string) __('Assign Merchant to %1 Customer(s)', count($customerIds))
        );
        return $resultPage;
    }
}
