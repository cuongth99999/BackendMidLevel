<?php
/**
 * app/code/Magenest/Merchant/Block/Adminhtml/Customer/MassAssignMerchant.php
 *
 * Backs the merchant-picker form. Pulls merchant options through the
 * shared source model (one query, no EAV round-trips per merchant) and
 * exposes the resolved customer count taken from the admin session.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Block\Adminhtml\Customer;

use Magenest\Merchant\Controller\Adminhtml\Customer\MassAssignMerchant\Edit;
use Magenest\Merchant\Model\Source\Customer\MerchantOptions;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session;

class MassAssignMerchant extends Template
{
    public function __construct(
        Context $context,
        private readonly Session $adminSession,
        private readonly MerchantOptions $merchantOptions,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getCustomerIds(): array
    {
        return (array) $this->adminSession->getData(Edit::SESSION_KEY_CUSTOMER_IDS, false);
    }

    public function getCustomerCount(): int
    {
        return count($this->getCustomerIds());
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function getMerchantOptions(): array
    {
        return $this->merchantOptions->getAllOptions();
    }

    public function getSaveUrl(): string
    {
        return $this->getUrl('magenest_merchant/customer_massassignmerchant/save');
    }

    public function getCancelUrl(): string
    {
        return $this->getUrl('customer/index/index');
    }
}
