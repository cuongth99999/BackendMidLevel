<?php

namespace Magenest\LayoutHandling\Plugin\Catalog\Product;

use Magento\Catalog\Helper\Product\View as ProductViewHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\RequestInterface;

/**
 *  AddCustomLayoutHandleToProductPlugin
 */
class AddCustomLayoutHandleToProductPlugin
{

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param  ProductViewHelper $subject
     * @param  Page            $resultPage
     * @param  Product         $product
     * @param  null|DataObject $params
     * @return array
     */
    public function beforeInitProductLayout(
        ProductViewHelper $subject,
                          $resultPage,
                          $product,
                          $params
    ) {
        if ($resultPage instanceof ResultPage && $this->request->getFullActionName() === 'catalog_product_view') {
            $price = (float)$product->getFinalPrice();

            $ranges = [
                ['min' => 0,   'max' => 50,  'handle' => 'catalog_product_view_price_0_50'],
                ['min' => 50,  'max' => 100, 'handle' => 'catalog_product_view_price_50_100'],
                ['min' => 100, 'max' => 200, 'handle' => 'catalog_product_view_price_100_200'],
                ['min' => 200, 'max' => 300, 'handle' => 'catalog_product_view_price_200_300'],
                ['min' => 300, 'max' => null, 'handle' => 'catalog_product_view_price_300_plus'],
            ];

            foreach ($ranges as $range) {
                if ($price >= $range['min'] && ($range['max'] === null || $price < $range['max'])) {
                    $resultPage->addHandle([$range['handle']]);
                    break;
                }
            }
        }
        return [
            $resultPage,
            $product,
            $params
        ];
    }
}
