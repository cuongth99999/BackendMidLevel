<?php
/**
 * app/code/Magenest/Merchant/Api/Data/MerchantSearchResultsInterface.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface MerchantSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Magenest\Merchant\Api\Data\MerchantInterface[]
     */
    public function getItems();

    /**
     * @param \Magenest\Merchant\Api\Data\MerchantInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
