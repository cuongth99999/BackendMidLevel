<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Block\Adminhtml\Event\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        $id = $this->getEventId();
        if (!$id) {
            return [];
        }
        $url = $this->getUrl('*/*/delete', ['id' => $id]);
        return [
            'label'      => __('Delete'),
            'class'      => 'delete',
            'on_click'   => sprintf(
                "deleteConfirm('%s', '%s')",
                __('Are you sure you want to delete this event?'),
                $url
            ),
            'sort_order' => 20,
        ];
    }
}
