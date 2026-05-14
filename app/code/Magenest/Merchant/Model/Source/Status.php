<?php
/**
 * app/code/Magenest/Merchant/Model/Source/Status.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Status extends AbstractSource
{
    public const ACTIVE   = 1;
    public const PENDING  = 2;
    public const BLOCKED  = 3;
    public const REJECTED = 4;

    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('-- Please Select --'), 'value' => ''],
                ['label' => __('Active'),   'value' => self::ACTIVE],
                ['label' => __('Pending'),  'value' => self::PENDING],
                ['label' => __('Blocked'),  'value' => self::BLOCKED],
                ['label' => __('Rejected'), 'value' => self::REJECTED],
            ];
        }
        return $this->_options;
    }
}
