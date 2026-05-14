<?php
/**
 * app/code/Magenest/Merchant/Model/MerchantRepository.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Model;

use Magenest\Merchant\Api\Data\MerchantInterface;
use Magenest\Merchant\Api\Data\MerchantSearchResultsInterface;
use Magenest\Merchant\Api\Data\MerchantSearchResultsInterfaceFactory;
use Magenest\Merchant\Api\MerchantRepositoryInterface;
use Magenest\Merchant\Model\ResourceModel\Merchant as MerchantResource;
use Magenest\Merchant\Model\ResourceModel\Merchant\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class MerchantRepository implements MerchantRepositoryInterface
{
    public function __construct(
        private readonly MerchantResource $resource,
        private readonly MerchantFactory $merchantFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly MerchantSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    public function save(MerchantInterface $merchant): MerchantInterface
    {
        try {
            $this->resource->save($merchant);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(__('Could not save the merchant: %1', $e->getMessage()), $e);
        }
        return $merchant;
    }

    public function getById(int $id): MerchantInterface
    {
        $merchant = $this->merchantFactory->create();
        $this->resource->load($merchant, $id);
        if (!$merchant->getId()) {
            throw new NoSuchEntityException(__('Merchant with id "%1" does not exist.', $id));
        }
        return $merchant;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): MerchantSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*');
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var MerchantSearchResultsInterface $results */
        $results = $this->searchResultsFactory->create();
        $results->setSearchCriteria($searchCriteria);
        $results->setItems($collection->getItems());
        $results->setTotalCount($collection->getSize());

        return $results;
    }

    public function delete(MerchantInterface $merchant): bool
    {
        try {
            $this->resource->delete($merchant);
        } catch (\Throwable $e) {
            throw new CouldNotDeleteException(__('Could not delete the merchant: %1', $e->getMessage()), $e);
        }
        return true;
    }

    public function deleteById(int $id): bool
    {
        return $this->delete($this->getById($id));
    }
}
