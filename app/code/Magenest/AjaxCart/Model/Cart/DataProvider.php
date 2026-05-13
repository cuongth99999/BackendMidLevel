<?php

// app/code/Magenest/AjaxCart/Model/Cart/DataProvider.php

declare(strict_types=1);

namespace Magenest\AjaxCart\Model\Cart;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Provides serialisable cart data (totals + visible items) for AJAX responses.
 *
 * Deliberately thin: all business logic stays in the Quote/Cart model layer.
 * Call only after the quote has been saved and collectTotals() has been invoked.
 */
class DataProvider
{
    public function __construct(
        private readonly CheckoutSession      $checkoutSession,
        private readonly PriceCurrencyInterface $priceCurrency
    ) {}

    /**
     * Return all active quote totals as formatted strings keyed by total code.
     *
     * Example output:
     *  [
     *    'subtotal'    => ['value' => 99.0, 'formatted' => '$99.00', 'title' => 'Subtotal'],
     *    'grand_total' => ['value' => 110.0, 'formatted' => '$110.00', 'title' => 'Grand Total'],
     *    'shipping'    => ['value' => 11.0, 'formatted' => '$11.00', 'title' => 'Shipping & Handling'],
     *  ]
     *
     * @return array<string, array{value: float, formatted: string, title: string}>
     */
    public function getTotalsData(): array
    {
        $quote  = $this->checkoutSession->getQuote();
        $totals = $quote->getTotals();
        $data   = [];

        foreach ($totals as $code => $total) {
            $value        = (float)$total->getValue();
            $data[$code]  = [
                'value'     => $value,
                // PERF: format() (no currency conversion) — values are already in display currency
                'formatted' => $this->priceCurrency->format($value, false),
                'title'     => (string)$total->getTitle(),
            ];
        }

        $data['items_count'] = (int)$quote->getItemsCount();
        $data['items_qty']   = (int)$quote->getItemsQty();

        return $data;
    }

    /**
     * Return unit price and row subtotal for every visible quote item.
     *
     * @return list<array{item_id: int, qty: float, price: string, subtotal: string}>
     */
    public function getItemsData(): array
    {
        $quote = $this->checkoutSession->getQuote();
        $items = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            $items[] = [
                'item_id'  => (int)$item->getId(),
                'qty'      => (float)$item->getQty(),
                // PERF: format() — already in display currency, skip conversion
                'price'    => $this->priceCurrency->format((float)$item->getPrice(), false),
                'subtotal' => $this->priceCurrency->format((float)$item->getRowTotal(), false),
            ];
        }

        return $items;
    }
}