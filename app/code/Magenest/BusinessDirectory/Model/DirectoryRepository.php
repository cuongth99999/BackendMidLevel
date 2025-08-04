<?php
declare(strict_types=1);

namespace Magenest\BusinessDirectory\Model;

use Magenest\BusinessDirectory\Api\Data\DirectoryInterface;
use Magenest\BusinessDirectory\Api\DirectoryRepositoryInterface;
use Magenest\BusinessDirectory\Model\ResourceModel\Directory as ResourceModel;
use Magenest\BusinessDirectory\Model\ResourceModel\Directory\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Cache\TypeListInterface;

class DirectoryRepository implements DirectoryRepositoryInterface
{
    private ResourceModel $resource;
    private DirectoryFactory $directoryFactory;
    private CollectionFactory $collectionFactory;
    private SearchResultsInterfaceFactory $searchResultsFactory;
    private CollectionProcessorInterface $collectionProcessor;
    private TypeListInterface $cacheTypeList;

    public function __construct(
        ResourceModel $resource,
        DirectoryFactory $directoryFactory,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        TypeListInterface $cacheTypeList
    ) {
        $this->resource = $resource;
        $this->directoryFactory = $directoryFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->cacheTypeList = $cacheTypeList;
    }

    public function save(DirectoryInterface $directory): DirectoryInterface
    {
        try {
            $this->resource->save($directory);
            // Clear both config and business_directory cache types
            $this->cacheTypeList->cleanType('config');
            $this->cacheTypeList->cleanType('business_directory');
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the directory: %1',
                $exception->getMessage()
            ));
        }
        return $directory;
    }

    public function getById(int $id): DirectoryInterface
    {
        $directory = $this->directoryFactory->create();
        $this->resource->load($directory, $id);
        if (!$directory->getId()) {
            throw new NoSuchEntityException(__('Directory with id "%1" does not exist.', $id));
        }
        return $directory;
    }

    public function delete(DirectoryInterface $directory): bool
    {
        try {
            $this->resource->delete($directory);
            // Clear both config and business_directory cache types
            $this->cacheTypeList->cleanType('config');
            $this->cacheTypeList->cleanType('business_directory');
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the directory: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    public function deleteById(int $id): bool
    {
        return $this->delete($this->getById($id));
    }

    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        
        return $searchResults;
    }
}