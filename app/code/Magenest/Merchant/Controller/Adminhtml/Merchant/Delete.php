<?php
/**
 * app/code/Magenest/Merchant/Controller/Adminhtml/Merchant/Delete.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Controller\Adminhtml\Merchant;

use Magenest\Merchant\Api\MerchantRepositoryInterface;
use Magenest\Merchant\Controller\Adminhtml\Merchant;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Merchant implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private readonly MerchantRepositoryInterface $merchantRepository
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $id = (int) $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                $this->merchantRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The merchant has been deleted.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This merchant no longer exists.'));
            } catch (\Throwable $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $redirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }

        return $redirect->setPath('*/*/index');
    }
}
