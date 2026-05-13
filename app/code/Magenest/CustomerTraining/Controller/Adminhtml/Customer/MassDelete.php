<?php
/**
 * app/code/Magenest/CustomerTraining/Controller/Adminhtml/Customer/MassDelete.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Controller\Adminhtml\Customer;

use Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface;
use Magenest\CustomerTraining\Model\ResourceModel\CustomerTraining\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenest_CustomerTraining::customer_training_manage';

    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        private readonly CustomerTrainingRepositoryInterface $repository
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $deleted = 0;
            foreach ($collection as $item) {
                $this->repository->delete($item);
                $deleted++;
            }
            $this->messageManager->addSuccessMessage(__('%1 record(s) have been deleted.', $deleted));
        } catch (\Throwable $e) {
            $this->messageManager->addExceptionMessage($e, __('Could not delete selected records.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
