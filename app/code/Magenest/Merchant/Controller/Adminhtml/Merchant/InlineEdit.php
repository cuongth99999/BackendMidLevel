<?php
/**
 * app/code/Magenest/Merchant/Controller/Adminhtml/Merchant/InlineEdit.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Controller\Adminhtml\Merchant;

use Magenest\Merchant\Api\MerchantRepositoryInterface;
use Magenest\Merchant\Controller\Adminhtml\Merchant;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class InlineEdit extends Merchant implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly MerchantRepositoryInterface $merchantRepository,
        private readonly DateTime $dateTime
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();
        $errors = [];

        if (!$this->getRequest()->getParam('isAjax')) {
            return $result->setData(['messages' => [__('Invalid request.')], 'error' => true]);
        }

        $items = (array) $this->getRequest()->getParam('items', []);
        foreach ($items as $id => $values) {
            try {
                $merchant = $this->merchantRepository->getById((int) $id);
                $values['latest_update_date'] = $this->dateTime->gmtDate();
                $merchant->addData($values);
                $this->merchantRepository->save($merchant);
            } catch (\Throwable $e) {
                $errors[] = __('[Merchant #%1] %2', $id, $e->getMessage());
            }
        }

        return $result->setData([
            'messages' => $errors,
            'error'    => (bool) $errors,
        ]);
    }
}
