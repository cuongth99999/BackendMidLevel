<?php
/**
 * app/code/Magenest/CustomerTraining/Block/Adminhtml/Customer/Edit/DeleteButton.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Block\Adminhtml\Customer\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        if (!$this->getEntityId()) {
            return [];
        }
        return [
            'label'      => __('Delete'),
            'class'      => 'delete',
            'on_click'   => sprintf(
                "deleteConfirm('%s', '%s')",
                __('Are you sure you want to delete this customer?'),
                $this->getUrl('*/*/delete', ['entity_id' => $this->getEntityId()])
            ),
            'sort_order' => 20,
        ];
    }
}
