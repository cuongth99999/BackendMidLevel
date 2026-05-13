<?php
/**
 * app/code/Magenest/CustomerTraining/Model/Resolver/Query/GetList.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Model\Resolver\Query;

use Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface;
use Magenest\CustomerTraining\Api\Data\CustomerTrainingInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetList implements ResolverInterface
{
    public function __construct(
        private readonly CustomerTrainingRepositoryInterface $repository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        $args = $args ?? [];
        $pageSize    = (int) ($args['pageSize']    ?? 20);
        $currentPage = (int) ($args['currentPage'] ?? 1);
        if ($pageSize < 1)    { throw new GraphQlInputException(__('"pageSize" must be at least 1.')); }
        if ($currentPage < 1) { throw new GraphQlInputException(__('"currentPage" must be at least 1.')); }

        $searchCriteria = $this->searchCriteriaBuilder->build('customerTrainings', $args);
        $searchCriteria->setPageSize($pageSize);
        $searchCriteria->setCurrentPage($currentPage);

        $result = $this->repository->getList($searchCriteria);

        $items = [];
        /** @var CustomerTrainingInterface $item */
        foreach ($result->getItems() as $item) {
            $items[] = $item->getData();
        }

        $totalCount = (int) $result->getTotalCount();
        $totalPages = $pageSize > 0 ? (int) ceil($totalCount / $pageSize) : 0;

        return [
            'total_count' => $totalCount,
            'items'       => $items,
            'page_info'   => [
                'page_size'    => $pageSize,
                'current_page' => $currentPage,
                'total_pages'  => $totalPages,
            ],
        ];
    }
}
