<?php
/**
 * app/code/Magenest/Merchant/Model/ResourceModel/Merchant/Grid/Collection.php
 *
 * Wraps the EAV collection so it can serve a UI listing data source.
 * Implements SearchResultInterface — required by the UI DataProvider contract.
 *
 * NOTE: every method signature here MUST match the parent interfaces exactly
 *       (no nullable param, no return type) — Magento\Framework\Api\Search\
 *       SearchResultInterface ultimately extends Api\SearchResultsInterface
 *       whose signatures use `Api\SearchCriteriaInterface` and no return hints.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Model\ResourceModel\Merchant\Grid;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magenest\Merchant\Model\ResourceModel\Merchant\Collection as MerchantCollection;

class Collection extends MerchantCollection implements SearchResultInterface
{
    /** @var AggregationInterface|null */
    protected $aggregations;

    /**
     * Select every EAV attribute by default so the grid columns have data.
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addAttributeToSelect('*');
        return $this;
    }

    /**
     * @return AggregationInterface|null
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    /**
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria)
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
