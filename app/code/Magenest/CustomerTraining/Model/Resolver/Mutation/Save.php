<?php
/**
 * app/code/Magenest/CustomerTraining/Model/Resolver/Mutation/Save.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Model\Resolver\Mutation;

use Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface;
use Magenest\CustomerTraining\Model\CustomerTraining;
use Magenest\CustomerTraining\Model\CustomerTrainingFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Save implements ResolverInterface
{
    public function __construct(
        private readonly CustomerTrainingRepositoryInterface $repository,
        private readonly CustomerTrainingFactory $modelFactory
    ) {
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        $input = $args['input'] ?? null;
        if (!is_array($input) || $input === []) {
            throw new GraphQlInputException(__('"input" is required.'));
        }

        $firstName = trim((string) ($input['first_name'] ?? ''));
        $lastName  = trim((string) ($input['last_name']  ?? ''));
        if ($firstName === '' || $lastName === '') {
            throw new GraphQlInputException(__('First Name and Last Name are required.'));
        }

        $age = null;
        if (array_key_exists('age', $input) && $input['age'] !== null) {
            $age = (int) $input['age'];
            if ($age < 0 || $age > 200) {
                throw new GraphQlInputException(__('Age must be between 0 and 200.'));
            }
        }

        $entityId = (int) ($input['entity_id'] ?? 0);

        try {
            /** @var CustomerTraining $model */
            $model = $entityId > 0
                ? $this->repository->getById($entityId)
                : $this->modelFactory->create();

            $model->setFirstName($firstName);
            $model->setLastName($lastName);
            if (array_key_exists('address', $input)) {
                $model->setAddress($input['address'] !== null ? (string) $input['address'] : null);
            }
            if (array_key_exists('city', $input)) {
                $model->setCity($input['city'] !== null ? (string) $input['city'] : null);
            }
            $model->setAge($age);

            $saved = $this->repository->save($model);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (GraphQlInputException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return $saved->getData();
    }
}
