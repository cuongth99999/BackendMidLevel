<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Block\Adminhtml\Event;

use Magenest\EventRegistration\Api\Data\EventInterface;
use Magenest\EventRegistration\Model\ResourceModel\Event\Collection;
use Magenest\EventRegistration\Model\ResourceModel\Event\CollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class Sidebar extends Template
{
    public function __construct(
        Context $context,
        private readonly CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return EventInterface[]
     */
    public function getEvents(): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->setOrder(EventInterface::SORT_ORDER, Collection::SORT_ORDER_ASC)
            ->setOrder(EventInterface::ENTITY_ID, Collection::SORT_ORDER_ASC);
        return $collection->getItems();
    }

    public function getCurrentEventId(): int
    {
        return (int) $this->getRequest()->getParam('id');
    }

    public function getEditUrl(int $id): string
    {
        return $this->getUrl('*/*/edit', ['id' => $id]);
    }

    public function getNewUrl(): string
    {
        return $this->getUrl('*/*/new');
    }
}
