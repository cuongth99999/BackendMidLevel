<?php
/**
 * app/code/Magenest/CustomerTraining/Block/Adminhtml/Customer/Edit/SaveAndContinueButton.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Block\Adminhtml\Customer\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndContinueButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        return [
            'label'          => __('Save and Continue Edit'),
            'class'          => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndContinueEdit'],
                ],
            ],
            'sort_order' => 80,
        ];
    }
}
