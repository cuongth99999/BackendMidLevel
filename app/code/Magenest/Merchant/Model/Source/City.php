<?php
/**
 * app/code/Magenest/Merchant/Model/Source/City.php
 *
 * Static list of Vietnamese provinces / centrally-controlled cities.
 * Replace this with a table-backed list if you need the full GSO dataset.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class City extends AbstractSource
{
    public const HA_NOI    = 1;
    public const HCM       = 79;
    public const DA_NANG   = 48;
    public const HAI_PHONG = 31;
    public const CAN_THO   = 92;

    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('-- Please Select --'),       'value' => ''],
                ['label' => __('Thành phố Hà Nội'),         'value' => self::HA_NOI],
                ['label' => __('Thành phố Hồ Chí Minh'),    'value' => self::HCM],
                ['label' => __('Thành phố Đà Nẵng'),        'value' => self::DA_NANG],
                ['label' => __('Thành phố Hải Phòng'),      'value' => self::HAI_PHONG],
                ['label' => __('Thành phố Cần Thơ'),        'value' => self::CAN_THO],
            ];
        }
        return $this->_options;
    }
}
