<?php
declare(strict_types=1);

namespace Magenest\BusinessDirectory\Api;

use Magenest\BusinessDirectory\Api\Data\DirectoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface DirectoryRepositoryInterface
{
    /**
     * Save directory
     *
     * @param DirectoryInterface $directory
     * @return DirectoryInterface
     * @throws LocalizedException
     */
    public function save(DirectoryInterface $directory): DirectoryInterface;

    /**
     * Get directory by ID
     *
     * @param int $id
     * @return DirectoryInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): DirectoryInterface;

    /**
     * Delete directory
     *
     * @param DirectoryInterface $directory
     * @return bool
     * @throws LocalizedException
     */
    public function delete(DirectoryInterface $directory): bool;

    /**
     * Delete directory by ID
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $id): bool;

    /**
     * Get directory list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;
}