<?php
/**
 * app/code/Magenest/Merchant/Model/Merchant.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Model;

use Magenest\Merchant\Api\Data\MerchantInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Merchant extends AbstractExtensibleModel implements MerchantInterface, IdentityInterface
{
    public const CACHE_TAG = 'magenest_merchant';

    /** @var string */
    protected $_eventPrefix = 'magenest_merchant';

    /** @var string */
    protected $_eventObject = 'merchant';

    protected function _construct(): void
    {
        $this->_init(\Magenest\Merchant\Model\ResourceModel\Merchant::class);
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getMerchantCode(): ?string
    {
        $value = $this->getData(self::MERCHANT_CODE);
        return $value === null ? null : (string) $value;
    }

    public function setMerchantCode(?string $value): self
    {
        return $this->setData(self::MERCHANT_CODE, $value);
    }

    public function getStoreName(): ?string
    {
        $value = $this->getData(self::STORE_NAME);
        return $value === null ? null : (string) $value;
    }

    public function setStoreName(?string $value): self
    {
        return $this->setData(self::STORE_NAME, $value);
    }

    public function getMcPhone(): ?string
    {
        $value = $this->getData(self::MC_PHONE);
        return $value === null ? null : (string) $value;
    }

    public function setMcPhone(?string $value): self
    {
        return $this->setData(self::MC_PHONE, $value);
    }

    public function getMerchantStatus(): ?int
    {
        $value = $this->getData(self::MERCHANT_STATUS);
        return $value === null ? null : (int) $value;
    }

    public function setMerchantStatus(?int $value): self
    {
        return $this->setData(self::MERCHANT_STATUS, $value);
    }
}
