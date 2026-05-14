<?php
/**
 * app/code/Magenest/Merchant/Controller/Adminhtml/Merchant/MassDelete.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Controller\Adminhtml\Merchant;

use Magenest\Merchant\Api\MerchantRepositoryInterface;
use Magenest\Merchant\Controller\Adminhtml\Merchant;
use Magenest\Merchant\Model\ResourceModel\Merchant\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Merchant implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        private readonly MerchantRepositoryInterface $merchantRepository
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;
        foreach ($collection->getItems() as $merchant) {
            try {
                $this->merchantRepository->delete($merchant);
                $deleted++;
            } catch (\Throwable $e) {
                $this->messageManager->addErrorMessage(
                    __('Failed to delete merchant #%1: %2', $merchant->getId(), $e->getMessage())
                );
            }
        }

        if ($deleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were deleted.', $deleted));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $redirect->setPath('*/*/index');
    }
}
