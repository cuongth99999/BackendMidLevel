<?php
/**
 * app/code/Magenest/Merchant/Model/Source/Product/MerchantOptions.php
 *
 * Source model for the `merchant` product attribute.
 *
 * The label of each option is the merchant store_name (plus its merchant_code
 * in parentheses). That label is what Magento's Elasticsearch fulltext indexer
 * picks up via `Magento\CatalogSearch\Model\Indexer\Fulltext\Action\
 * DataProvider::getAttributeOptionValue()` — it builds a `value=>label` map by
 * calling `$attribute->getSource()->toOptionArray()` and appends the matching
 * label to the indexed text. So when a product has `merchant=5`, ES indexes
 * "Smile Store Hà Nội (00181602)" as searchable text for that product.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Model\Source\Product;

use Magenest\Merchant\Api\Data\MerchantInterface;
use Magenest\Merchant\Model\ResourceModel\Merchant\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class MerchantOptions extends AbstractSource
{
    public function __construct(
        private readonly CollectionFactory $merchantCollectionFactory
    ) {
    }

    public function getAllOptions(): array
    {
        if ($this->_options !== null) {
            return $this->_options;
        }

        $options = [
            ['value' => '', 'label' => __('-- Please Select --')],
        ];

        $collection = $this->merchantCollectionFactory->create();
        $collection->addAttributeToSelect([
            MerchantInterface::STORE_NAME,
            MerchantInterface::MERCHANT_CODE,
        ]);
        $collection->setOrder('entity_id', 'ASC');

        foreach ($collection as $merchant) {
            $storeName = (string) $merchant->getData(MerchantInterface::STORE_NAME);
            $code      = (string) $merchant->getData(MerchantInterface::MERCHANT_CODE);
            $label = $code !== '' ? sprintf('%s (%s)', $storeName, $code) : $storeName;
            $options[] = [
                'value' => (string) $merchant->getId(),
                'label' => $label,
            ];
        }

        $this->_options = $options;
        return $this->_options;
    }
}
