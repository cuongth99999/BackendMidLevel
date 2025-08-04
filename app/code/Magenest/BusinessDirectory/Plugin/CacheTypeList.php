<?php
declare(strict_types=1);

namespace Magenest\BusinessDirectory\Plugin;

use Magento\Framework\App\Cache\TypeListInterface;
use Magenest\BusinessDirectory\Model\Cache\Type as BusinessDirectoryCache;

class CacheTypeList
{
    /**
     * After clean config cache, also clean business directory cache
     *
     * @param TypeListInterface $subject
     * @param mixed $result
     * @param string $type
     * @return mixed
     */
    public function afterCleanType(
        TypeListInterface $subject,
        $result,
        $type
    ) {
        if ($type === 'config') {
            $subject->cleanType(BusinessDirectoryCache::TYPE_IDENTIFIER);
        }
        return $result;
    }
}
