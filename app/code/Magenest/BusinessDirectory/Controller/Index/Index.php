<?php
declare(strict_types=1);

namespace Magenest\BusinessDirectory\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magenest\BusinessDirectory\Model\Cache\Type as CacheType;
use Magenest\BusinessDirectory\Api\DirectoryRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Index implements HttpGetActionInterface
{
    private PageFactory $resultPageFactory;
    private TypeListInterface $cacheTypeList;
    private DirectoryRepositoryInterface $directoryRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        PageFactory $resultPageFactory,
        TypeListInterface $cacheTypeList,
        DirectoryRepositoryInterface $directoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->directoryRepository = $directoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Directory list page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Check if data exists in cache
        $cacheType = CacheType::TYPE_IDENTIFIER;
        
        // Get data using repository
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $directoryList = $this->directoryRepository->getList($searchCriteria);

        // Cache will be handled by the block and repository layer
        
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Business Directory'));

        return $resultPage;
    }
}