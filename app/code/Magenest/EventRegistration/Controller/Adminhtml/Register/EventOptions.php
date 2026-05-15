<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Controller\Adminhtml\Register;

use Magenest\EventRegistration\Api\Data\EventInterface;
use Magenest\EventRegistration\Model\ResourceModel\Event\Collection;
use Magenest\EventRegistration\Model\ResourceModel\Event\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class EventOptions extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenest_EventRegistration::event_register';

    public function __construct(
        Context $context,
        private readonly CollectionFactory $collectionFactory,
        private readonly JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->setOrder(EventInterface::SORT_ORDER, Collection::SORT_ORDER_ASC)
            ->setOrder(EventInterface::ENTITY_ID, Collection::SORT_ORDER_ASC);

        $events = [];
        /** @var EventInterface $event */
        foreach ($collection->getItems() as $event) {
            $events[] = [
                'id'         => (int) $event->getEntityId(),
                'name'       => (string) $event->getName(),
                'event_date' => (string) $event->getEventDate(),
            ];
        }

        return $this->jsonFactory->create()->setData(['events' => $events]);
    }
}
