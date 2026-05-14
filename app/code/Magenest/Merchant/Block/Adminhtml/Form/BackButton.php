<?php
/**
 * app/code/Magenest/Merchant/Block/Adminhtml/Form/BackButton.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Block\Adminhtml\Form;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BackButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        return [
            'label'      => __('Back'),
            'on_click'   => sprintf("location.href = '%s';", $this->getUrl('*/*/index')),
            'class'      => 'back',
            'sort_order' => 10,
        ];
    }
}
