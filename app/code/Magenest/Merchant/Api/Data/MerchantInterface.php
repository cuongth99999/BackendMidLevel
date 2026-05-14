<?php
/**
 * app/code/Magenest/Merchant/Api/Data/MerchantInterface.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Api\Data;

interface MerchantInterface
{
    public const ENTITY_ID          = 'entity_id';
    public const MERCHANT_CODE      = 'merchant_code';
    public const STORE_NAME         = 'store_name';
    public const MC_PHONE           = 'mc_phone';
    public const CATEGORY_IDS       = 'category_ids';
    public const ACTIVE_DATE        = 'active_date';
    public const LATEST_UPDATE_DATE = 'latest_update_date';
    public const ONBOARDING_DATE    = 'onboarding_date';
    public const MERCHANT_STATUS    = 'merchant_status';
    public const KYC_LEVEL          = 'kyc_level';
    public const MERCHANT_TYPE      = 'merchant_type';
    public const PARTNER            = 'partner';
    public const DSA_PHONE          = 'dsa_phone';
    public const CITY               = 'city';
    public const DISTRICT           = 'district';
    public const WARD               = 'ward';
    public const CREATED_AT         = 'created_at';
    public const UPDATED_AT         = 'updated_at';

    public function getId();

    public function setId($id);

    public function getMerchantCode(): ?string;

    public function setMerchantCode(?string $value): self;

    public function getStoreName(): ?string;

    public function setStoreName(?string $value): self;

    public function getMcPhone(): ?string;

    public function setMcPhone(?string $value): self;

    public function getMerchantStatus(): ?int;

    public function setMerchantStatus(?int $value): self;
}
