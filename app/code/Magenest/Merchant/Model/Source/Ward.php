<?php
/**
 * app/code/Magenest/Merchant/Model/Source/Ward.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Ward extends AbstractSource
{
    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('-- Please Select --'),     'value' => ''],

                // Hai Bà Trưng - HN
                ['label' => __('Đê La Thành'),     'value' => 10101],
                ['label' => __('Trương Định'),     'value' => 10102],
                ['label' => __('Vĩnh Tuy'),        'value' => 10103],

                // Hoàn Kiếm - HN
                ['label' => __('Tràng Tiền'),      'value' => 10201],
                ['label' => __('Hàng Bài'),        'value' => 10202],

                // Quận 1 - HCM
                ['label' => __('Bến Nghé'),        'value' => 20101],
                ['label' => __('Bến Thành'),       'value' => 20102],

                // Quận 7 - HCM
                ['label' => __('Tân Phong'),       'value' => 20301],
            ];
        }
        return $this->_options;
    }
}
