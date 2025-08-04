<?php
declare(strict_types=1);

namespace Magenest\BusinessDirectory\Block\Directory;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magenest\BusinessDirectory\Api\DirectoryRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Cache\Type\Config as ConfigCache;

class Listing extends Template
{
    private DirectoryRepositoryInterface $directoryRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        Context $context,
        DirectoryRepositoryInterface $directoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->directoryRepository = $directoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get directory list
     * @return \Magento\Framework\Api\ExtensibleDataInterface[]|mixed
     */
    public function getDirectoryList()
    {
        $cacheKey = 'business_directory';

        // Try to get data from cache
        $cache = $this->_cache;
        $directoryData = $cache->load($cacheKey);

        if ($directoryData) {
            return unserialize($directoryData);
        }

        // If not in cache, get from repository
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->directoryRepository->getList($searchCriteria);
        $items = $searchResults->getItems();

        // Save to cache
        $cache->save(
            serialize($items),
            $cacheKey,
            [
                ConfigCache::CACHE_TAG,
                \Magenest\BusinessDirectory\Model\Directory::CACHE_TAG
            ]
        );

        return $items;
    }

    /**
     * Get consumer frontend value
     *
     * @param bool $value
     * @return \Magento\Framework\Phrase
     */
    public function getConsumerFrontendValue(bool $value)
    {
        return $value ? __('Yes') : __('No');
    }

    /**
     * Get business frontend value
     *
     * @param bool $value
     * @return \Magento\Framework\Phrase
     */
    public function getBusinessFrontendValue(bool $value)
    {
        return $value ? __('Yes') : __('No');
    }
}
