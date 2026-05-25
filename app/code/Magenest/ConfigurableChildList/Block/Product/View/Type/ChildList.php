<?php
/**
 * app/code/Magenest/ConfigurableChildList/Block/Product/View/Type/ChildList.php
 */
declare(strict_types=1);

namespace Magenest\ConfigurableChildList\Block\Product\View\Type;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Helper\Product as CatalogProductHelper;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as ConfigurableBlock;
use Magento\ConfigurableProduct\Helper\Data as ConfigurableHelper;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\Format;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;

/**
 * Provides JSON payload powering the per-child product list rendered in place of
 * the last configurable attribute.
 */
class ChildList extends ConfigurableBlock
{
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        ConfigurableHelper $helper,
        CatalogProductHelper $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        private readonly ImageHelper $imageHelper,
        private readonly CartHelper $cartHelper,
        private readonly FormKey $formKey,
        array $data = [],
        ?Format $localeFormat = null,
        ?Session $customerSession = null,
        ?Prices $variationPrices = null
    ) {
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $data,
            $localeFormat,
            $customerSession,
            $variationPrices
        );
    }

    /**
     * Whether the block should render (only for configurable products with attrs/children).
     */
    public function shouldRender(): bool
    {
        $product = $this->getProduct();
        if (!$product || $product->getTypeId() !== \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return false;
        }
        return count($this->getAllowAttributes()) > 0 && count($this->getAllowProducts()) > 0;
    }

    /**
     * Build the JSON used by the JS widget to filter and render child cards.
     */
    public function getChildListConfig(): string
    {
        $product = $this->getProduct();

        $attributes = $this->getAllowAttributes();
        $attrMeta = [];
        $orderedAttrIds = [];
        foreach ($attributes as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attrId = (int) $productAttribute->getAttributeId();
            $attrMeta[$attrId] = [
                'id'    => $attrId,
                'code'  => (string) $productAttribute->getAttributeCode(),
                'label' => (string) $productAttribute->getStoreLabel(),
            ];
            $orderedAttrIds[] = $attrId;
        }
        // SEC: the "last" attribute is derived server-side from attribute position;
        // JS only consumes it as an ID to hide the field — no privilege impact.
        $lastAttributeId = $orderedAttrIds ? end($orderedAttrIds) : null;

        $children = [];
        foreach ($this->getAllowProducts() as $child) {
            $childAttrs = [];
            foreach ($attributes as $attribute) {
                $code = $attribute->getProductAttribute()->getAttributeCode();
                $childAttrs[(int) $attribute->getProductAttribute()->getAttributeId()]
                    = (int) $child->getData($code);
            }

            // PERF: rely on already-loaded price info on the child product;
            // avoids a second collection load.
            $finalPriceAmount = $child->getPriceInfo()
                ->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
                ->getAmount()
                ->getValue();

            $children[] = [
                'id'                => (int) $child->getId(),
                'sku'               => (string) $child->getSku(),
                'name'              => (string) $child->getName(),
                'short_description' => trim(strip_tags((string) $child->getShortDescription())),
                'image'             => $this->imageHelper->init($child, 'product_small_image')->getUrl(),
                'price'             => $this->priceCurrency->format(
                    (float) $finalPriceAmount,
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION
                ),
                'price_raw'         => (float) $finalPriceAmount,
                'attributes'        => $childAttrs,
                'in_stock'          => (bool) $child->isAvailable(),
            ];
        }

        return $this->jsonEncoder->encode([
            'productId'       => (int) $product->getId(),
            'addToCartUrl'    => $this->cartHelper->getAddUrl($product),
            'formKey'         => $this->formKey->getFormKey(),
            'attributes'      => $attrMeta,
            'orderedAttrIds'  => $orderedAttrIds,
            'lastAttributeId' => $lastAttributeId,
            'children'        => $children,
            'i18n'            => [
                'empty'      => (string) __('Please choose the options above to see the available products.'),
                'noMatch'    => (string) __('No matching products are available for this combination.'),
                'sku'        => (string) __('SKU'),
                'addToCart'  => (string) __('Add to Cart'),
                'outOfStock' => (string) __('Out of Stock'),
                'added'      => (string) __('Item added to your cart.'),
                'genericErr' => (string) __('Could not add the item to your cart. Please try again.'),
                'qty'        => (string) __('Qty'),
            ],
        ]);
    }
}
