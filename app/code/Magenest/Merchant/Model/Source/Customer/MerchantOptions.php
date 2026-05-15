<?php
/**
 * app/code/Magenest/Merchant/Model/Source/Customer/MerchantOptions.php
 *
 * Source model for the customer `merchant_id` attribute. Same shape as
 * the product-side source: option value = merchant entity_id, label =
 * "Store Name (merchant_code)". Kept as a separate class (not reused
 * from Product/) because the source_model column is persisted into
 * eav_attribute, and we want each entity's attribute to be cleanly
 * traceable to its own namespace.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Model\Source\Customer;

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
