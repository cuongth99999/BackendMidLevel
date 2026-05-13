<?php

// app/code/Magenest/AjaxCart/Controller/Cart/AjaxUpdate.php

declare(strict_types=1);

namespace Magenest\AjaxCart\Controller\Cart;

use Magenest\AjaxCart\Model\Cart\DataProvider;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class AjaxUpdate implements HttpPostActionInterface
{
    public function __construct(
        private readonly RequestInterface        $request,
        private readonly JsonFactory             $resultJsonFactory,
        private readonly FormKeyValidator        $formKeyValidator,
        private readonly Cart                    $cart,
        private readonly RequestQuantityProcessor $quantityProcessor,
        private readonly DataProvider            $cartDataProvider,
        private readonly LoggerInterface         $logger
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

        try {
            $cartData = $this->request->getParam('cart');

            if (is_array($cartData)) {
                // SEC: Guest session may carry stale customer ID from a previous login
                if (!$this->cart->getCustomerSession()->getCustomerId()
                    && $this->cart->getQuote()->getCustomerId()
                ) {
                    $this->cart->getQuote()->setCustomerId(null);
                }

                $cartData = $this->quantityProcessor->process($cartData);
                $cartData = $this->cart->suggestItemsQty($cartData);
                $this->cart->updateItems($cartData)->save();
            }

            // PERF: Force re-collection so totals reflect the just-saved state
            $this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals();

            return $result->setData([
                'success' => true,
                'message' => (string)__('Shopping cart has been updated.'),
                'totals'  => $this->cartDataProvider->getTotalsData(),
                'items'   => $this->cartDataProvider->getItemsData(),
            ]);
        } catch (LocalizedException $e) {
            $this->cart->save();

            return $result->setData([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            $this->logger->critical($e);

            return $result->setData([
                'success' => false,
                'message' => (string)__('We can\'t update the shopping cart.'),
            ]);
        }
    }
}
