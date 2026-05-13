<?php
/**
 * app/code/Magenest/CustomerTraining/Controller/Adminhtml/Customer/Save.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Controller\Adminhtml\Customer;

use Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface;
use Magenest\CustomerTraining\Model\CustomerTraining;
use Magenest\CustomerTraining\Model\CustomerTrainingFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenest_CustomerTraining::customer_training_manage';

    public function __construct(
        Context $context,
        private readonly CustomerTrainingFactory $modelFactory,
        private readonly CustomerTrainingRepositoryInterface $repository,
        private readonly DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        // Magento UI form serializes fields flat at the top of POST, but
        // also stays compatible when posted under a `data` wrapper.
        if (isset($data['data']) && is_array($data['data'])) {
            $data = array_merge($data, $data['data']);
        }

        $entityId = isset($data['entity_id']) ? (int) $data['entity_id'] : 0;

        try {
            /** @var CustomerTraining $model */
            $model = $entityId
                ? $this->repository->getById($entityId)
                : $this->modelFactory->create();

            $firstName = trim((string) ($data['first_name'] ?? ''));
            $lastName  = trim((string) ($data['last_name']  ?? ''));
            if ($firstName === '' || $lastName === '') {
                throw new LocalizedException(__('First Name and Last Name are required.'));
            }

            $age = isset($data['age']) && $data['age'] !== '' ? (int) $data['age'] : null;
            if ($age !== null && ($age < 0 || $age > 200)) {
                throw new LocalizedException(__('Age must be between 0 and 200.'));
            }

            $model->setFirstName($firstName);
            $model->setLastName($lastName);
            $model->setAddress(isset($data['address']) ? (string) $data['address'] : null);
            $model->setCity(isset($data['city']) ? (string) $data['city'] : null);
            $model->setAge($age);

            $saved = $this->repository->save($model);
            $this->messageManager->addSuccessMessage(__('The customer has been saved.'));

            $this->dataPersistor->clear('magenest_customer_training');

            if ($request->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $saved->getEntityId()]);
            }
            return $resultRedirect->setPath('*/*/');
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This customer no longer exists.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('magenest_customer_training', $data);
            if ($entityId) {
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $entityId]);
            }
            return $resultRedirect->setPath('*/*/new');
        } catch (\Throwable $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the customer.'));
            $this->dataPersistor->set('magenest_customer_training', $data);
            if ($entityId) {
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $entityId]);
            }
            return $resultRedirect->setPath('*/*/new');
        }

        return $resultRedirect->setPath('*/*/');
    }
}
