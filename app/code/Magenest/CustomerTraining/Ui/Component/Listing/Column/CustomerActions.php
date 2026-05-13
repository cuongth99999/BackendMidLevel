<?php
/**
 * app/code/Magenest/CustomerTraining/Ui/Component/Listing/Column/CustomerActions.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CustomerActions extends Column
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
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (!isset($item['entity_id'])) {
                    continue;
                }
                $name = $this->getData('name');
                $item[$name]['edit'] = [
                    'href'  => $this->urlBuilder->getUrl(
                        'customertraining/customer/edit',
                        ['entity_id' => $item['entity_id']]
                    ),
                    'label' => __('Edit'),
                ];
                $fullName = trim(
                    (string) ($item['first_name'] ?? '') . ' ' . (string) ($item['last_name'] ?? '')
                );
                $item[$name]['delete'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'customertraining/customer/delete',
                        ['entity_id' => $item['entity_id']]
                    ),
                    'label'   => __('Delete'),
                    'confirm' => [
                        'title'   => $fullName !== ''
                            ? __('Delete "%1"', $fullName)
                            : __('Delete customer'),
                        'message' => __('Are you sure you want to delete this customer?'),
                    ],
                ];
            }
        }
        return $dataSource;
    }
}
