<?php
/**
 * app/code/Magenest/Merchant/Block/Adminhtml/Form/SaveButton.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Block\Adminhtml\Form;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        return [
            'label'      => __('Save Merchant'),
            'class'      => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
