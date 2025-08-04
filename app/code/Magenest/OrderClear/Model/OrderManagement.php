<?php
declare(strict_types=1);

namespace Magenest\OrderClear\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class OrderManagement implements OrderManagementInterface
{
    private CollectionFactory $orderCollectionFactory;
    private OrderRepository $orderRepository;
    private DateTime $dateTime;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        OrderRepository $orderRepository,
        DateTime $dateTime
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @inheritDoc
     */
    public function cancelOrdersByStatus(string $status): int
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $oneHourAgo = $this->dateTime->gmtDate('Y-m-d H:i:s', strtotime('-1 hour'));

        // Get orders with specified status not updated in last hour
        $orderCollection->addFieldToFilter('status', $status)
            ->addFieldToFilter('updated_at', ['lt' => $oneHourAgo]);

        $cancelledCount = 0;

        foreach ($orderCollection as $order) {
            try {
                if ($order->canCancel()) {
                    $order->cancel();
                    $this->orderRepository->save($order);
                    $cancelledCount++;
                }
            } catch (LocalizedException $e) {
                // Log error if needed
                continue;
            }
        }

        return $cancelledCount;
    }
}