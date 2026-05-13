<?php

// app/code/Magenest/AjaxCart/Controller/Cart/AjaxDelete.php

declare(strict_types=1);

namespace Magenest\AjaxCart\Controller\Cart;

use Magenest\AjaxCart\Model\Cart\DataProvider;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Psr\Log\LoggerInterface;

class AjaxDelete implements HttpPostActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly JsonFactory      $resultJsonFactory,
        private readonly FormKeyValidator $formKeyValidator,
        private readonly Cart             $cart,
        private readonly DataProvider     $cartDataProvider,
        private readonly LoggerInterface  $logger
    ) {}

    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();

        // SEC: Validate form key on every state-mutating POST
        if (!$this->formKeyValidator->validate($this->request)) {
            return $result->setData([
                'success' => false,
                'message' => (string)__('Invalid form key. Please refresh the page.'),
            ]);
        }

        // SEC: Cast to int to prevent injection via non-numeric IDs
        $itemId = (int)$this->request->getParam('id');

        if (!$itemId) {
            return $result->setData([
                'success' => false,
                'message' => (string)__('Invalid item.'),
            ]);
        }

        try {
            $this->cart->removeItem($itemId);

            // PERF: Reset flag so next collectTotals() does not use stale cache
            $this->cart->getQuote()->setTotalsCollectedFlag(false);
            $this->cart->save();
            $this->cart->getQuote()->collectTotals();

            $isEmpty = count($this->cart->getQuote()->getAllVisibleItems()) === 0;

            return $result->setData([
                'success'  => true,
                'message'  => (string)__('Item has been removed from your shopping cart.'),
                'is_empty' => $isEmpty,
                'totals'   => $this->cartDataProvider->getTotalsData(),
                'items'    => $this->cartDataProvider->getItemsData(),
            ]);
        } catch (\Exception $e) {
            $this->logger->critical($e);

            return $result->setData([
                'success' => false,
                'message' => (string)__('We can\'t remove the item.'),
            ]);
        }
    }
}