<?php
/**
 * app/code/Magenest/Merchant/Controller/Adminhtml/Merchant/Save.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Controller\Adminhtml\Merchant;

use Magenest\Merchant\Api\Data\MerchantInterface;
use Magenest\Merchant\Api\MerchantRepositoryInterface;
use Magenest\Merchant\Controller\Adminhtml\Merchant;
use Magenest\Merchant\Model\MerchantFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Save extends Merchant implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private readonly MerchantRepositoryInterface $merchantRepository,
        private readonly MerchantFactory $merchantFactory,
        private readonly DateTime $dateTime
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $data = (array) $this->getRequest()->getPostValue();

        if (!$data) {
            return $redirect->setPath('*/*/index');
        }

        try {
            $id = isset($data['entity_id']) ? (int) $data['entity_id'] : 0;
            $model = $id
                ? $this->merchantRepository->getById($id)
                : $this->merchantFactory->create();

            // Normalize multiselect category_ids
            if (isset($data[MerchantInterface::CATEGORY_IDS]) && is_array($data[MerchantInterface::CATEGORY_IDS])) {
                $data[MerchantInterface::CATEGORY_IDS] = implode(',', array_filter($data[MerchantInterface::CATEGORY_IDS]));
            }

            // Auto-stamp Latest Update Date
            $data[MerchantInterface::LATEST_UPDATE_DATE] = $this->dateTime->gmtDate();

            // First Active Date defaults to now if missing
            if (!$id && empty($data[MerchantInterface::ACTIVE_DATE])) {
                $data[MerchantInterface::ACTIVE_DATE] = $this->dateTime->gmtDate();
            }

            // SEC: never trust submitted entity_id beyond identifying which record to load
            unset($data['form_key']);
            $model->addData($data);
            $this->merchantRepository->save($model);

            $this->messageManager->addSuccessMessage(__('Merchant saved successfully.'));
            $this->_getSession()->setFormData(false);

            if ($this->getRequest()->getParam('back')) {
                return $redirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
            }
            return $redirect->setPath('*/*/index');
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This merchant no longer exists.'));
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Could not save the merchant: %1', $e->getMessage()));
            $this->_getSession()->setFormData($data);
            return $redirect->setPath('*/*/edit', ['entity_id' => (int) ($data['entity_id'] ?? 0)]);
        }

        return $redirect->setPath('*/*/index');
    }
}
