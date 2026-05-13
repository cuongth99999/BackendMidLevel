<?php
/**
 * app/code/Magenest/CustomerTraining/Api/Data/CustomerTrainingSearchResultsInterface.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface CustomerTrainingSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return CustomerTrainingInterface[]
     */
    public function getItems();

    /**
     * @param CustomerTrainingInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
