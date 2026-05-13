<?php
/**
 * app/code/Magenest/CustomerTraining/Model/Resolver/Mutation/Delete.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Model\Resolver\Mutation;

use Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Delete implements ResolverInterface
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
            $this->repository->deleteById($entityId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (\Throwable $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            'success' => true,
            'message' => (string) __('The customer training entry with ID %1 has been deleted.', $entityId),
        ];
    }
}
