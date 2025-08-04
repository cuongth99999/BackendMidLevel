<?php
declare(strict_types=1);

namespace Magenest\OrderClear\Model;

interface OrderManagementInterface
{
    /**
     * Cancel orders with specific status that haven't been updated in last hour
     *
     * @param string $status
     * @return int Number of orders cancelled
     */
    public function cancelOrdersByStatus(string $status): int;
}