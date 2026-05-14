<?php
/**
 * app/code/Magenest/Merchant/Block/Adminhtml/Form/DeleteButton.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Block\Adminhtml\Form;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        if (!$this->getMerchantId()) {
            return [];
        }

        $confirm = __('Are you sure you want to delete this merchant?');
        $url = $this->getUrl('*/*/delete', ['entity_id' => $this->getMerchantId()]);

        return [
            'label'      => __('Delete Merchant'),
            'class'      => 'delete',
            'on_click'   => sprintf("deleteConfirm('%s', '%s')", $confirm, $url),
            'sort_order' => 20,
        ];
    }
}
