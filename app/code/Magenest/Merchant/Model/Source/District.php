<?php
/**
 * app/code/Magenest/Merchant/Model/Source/District.php
 *
 * Sample VN districts. In production this is typically backed by a table and
 * cascaded from the City value via Knockout UI dependencies.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class District extends AbstractSource
{
    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('-- Please Select --'),    'value' => ''],

                // Hà Nội
                ['label' => __('Hai Bà Trưng'),   'value' => 101],
                ['label' => __('Hoàn Kiếm'),      'value' => 102],
                ['label' => __('Đống Đa'),         'value' => 103],
                ['label' => __('Cầu Giấy'),       'value' => 104],
                ['label' => __('Ba Đình'),        'value' => 105],

                // Hồ Chí Minh
                ['label' => __('Quận 1'),         'value' => 201],
                ['label' => __('Quận 3'),         'value' => 202],
                ['label' => __('Quận 7'),         'value' => 203],
                ['label' => __('Bình Thạnh'),     'value' => 204],
                ['label' => __('Thủ Đức'),        'value' => 205],

                // Đà Nẵng
                ['label' => __('Hải Châu'),       'value' => 301],
                ['label' => __('Thanh Khê'),      'value' => 302],
            ];
        }
        return $this->_options;
    }
}
