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
     * Get the page of matching customer training records.
     *
     * @return \Magenest\CustomerTraining\Api\Data\CustomerTrainingInterface[]
     */
    public function getItems();

    /**
     * Set the page of matching customer training records.
     *
     * @param \Magenest\CustomerTraining\Api\Data\CustomerTrainingInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
