<?php
/**
 * app/code/Magenest/Merchant/Ui/Component/Listing/Column/MerchantActions.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class MerchantActions extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $id = $item['entity_id'] ?? null;
            if (!$id) {
                continue;
            }
            $item[$this->getData('name')] = [
                'edit' => [
                    'href'  => $this->urlBuilder->getUrl('magenest_merchant/merchant/edit', ['entity_id' => $id]),
                    'label' => __('Edit'),
                ],
                'delete' => [
                    'href'    => $this->urlBuilder->getUrl('magenest_merchant/merchant/delete', ['entity_id' => $id]),
                    'label'   => __('Delete'),
                    'confirm' => [
                        'title'   => __('Delete merchant #%1', $id),
                        'message' => __('Are you sure you want to delete this merchant?'),
                    ],
                    'post' => true,
                ],
            ];
        }

        return $dataSource;
    }
}
