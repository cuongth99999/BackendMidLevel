<?php
/**
 * app/code/Magenest/CustomerTraining/Model/Resolver/Query/Get.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Model\Resolver\Query;

use Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Get implements ResolverInterface
{
    public function __construct(
        private readonly CustomerTrainingRepositoryInterface $repository
    ) {
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        $entityId = (int) ($args['entity_id'] ?? 0);
        if ($entityId <= 0) {
            throw new GraphQlInputException(__('"entity_id" must be a positive integer.'));
        }

        try {
            $entity = $this->repository->getById($entityId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $entity->getData();
    }
}
