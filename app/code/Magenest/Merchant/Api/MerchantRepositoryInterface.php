<?php
/**
 * app/code/Magenest/Merchant/Api/MerchantRepositoryInterface.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Api;

use Magenest\Merchant\Api\Data\MerchantInterface;
use Magenest\Merchant\Api\Data\MerchantSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface MerchantRepositoryInterface
{
    /**
     * @throws CouldNotSaveException
     */
    public function save(MerchantInterface $merchant): MerchantInterface;

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $id): MerchantInterface;

    public function getList(SearchCriteriaInterface $searchCriteria): MerchantSearchResultsInterface;

    /**
     * @throws CouldNotDeleteException
     */
    public function delete(MerchantInterface $merchant): bool;

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $id): bool;
}
