<?php

namespace Magenest\FullPageCache\Plugin\Product\Configurable;


use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

class AddMoreDataForSwatchConfig
{
    /**
     * @var Json
     */
    protected $json;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    protected $getStockIdForCurrentWebsite;

    /**
     * @var GetProductSalableQtyInterface
     */
    protected $getProductSalableQty;

    /**
     * @param Json $json
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param GetProductSalableQtyInterface $getProductSalableQty
     */
    public function __construct(
        Json $json,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        GetProductSalableQtyInterface $getProductSalableQty
    ) {
        $this->json = $json;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->getProductSalableQty = $getProductSalableQty;
    }

    /**
     * @param Configurable $subject
     * @param string $result
     * @return string
     * @throws NoSuchEntityException
     */
    public function afterGetJsonConfig(Configurable $subject, string $result): string
    {
        $result = $this->json->unserialize($result);
        $stockId = (int)$this->getStockIdForCurrentWebsite->execute();
        $childSalableQty = [];
        $products = $subject->getAllowProducts();
        foreach ($products as $product) {
            $qty = (float)$this->getProductSalableQty->execute($product->getSku(), $stockId);
            $childSalableQty[$product->getId()] = $qty;
        }
        $result['childSalableQty'] = $childSalableQty;
        return $this->json->serialize($result);
    }
}
