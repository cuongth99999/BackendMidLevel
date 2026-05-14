<?php
/**
 * app/code/Magenest/Merchant/Setup/Patch/Data/AddSampleMerchants.php
 *
 * Seeds 10 sample merchants using raw SQL — we do NOT go through the EAV
 * model layer because, in the same `setup:upgrade` run, the resource model's
 * cached `_type` was empty (EavConfig had loaded it before this module's
 * `eav_entity_type` row existed) which made model->save() route every field
 * to the entity table and skip the value tables.
 *
 * Idempotent: each row is keyed by `merchant_code` and skipped if already
 * present in `magenest_merchant_entity_varchar`.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Setup\Patch\Data;

use Magenest\Merchant\Api\Data\MerchantInterface;
use Magenest\Merchant\Model\Source\City;
use Magenest\Merchant\Model\Source\Status;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddSampleMerchants implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();
        self::seed($this->moduleDataSetup);
        $this->moduleDataSetup->endSetup();
        return $this;
    }

    /**
     * Reusable inserter — also called from RepairSampleMerchants.
     *
     * Walks the sample rows and inserts:
     *   - one row into magenest_merchant_entity
     *   - one row per non-null field into the matching value table
     *     (_varchar / _int / _text / _datetime), keyed on the EAV attribute_id
     *     and backend_type pulled from `eav_attribute`.
     */
    public static function seed(ModuleDataSetupInterface $setup): void
    {
        $connection = $setup->getConnection();

        $entityTable        = $setup->getTable('magenest_merchant_entity');
        $eavEntityTypeTable = $setup->getTable('eav_entity_type');
        $eavAttrSetTable    = $setup->getTable('eav_attribute_set');
        $eavAttrTable       = $setup->getTable('eav_attribute');
        $varcharTable       = $setup->getTable('magenest_merchant_entity_varchar');

        // 1. Resolve entity_type_id from DB (NOT from EavConfig — see file header).
        $entityTypeId = (int) $connection->fetchOne(
            $connection->select()
                ->from($eavEntityTypeTable, 'entity_type_id')
                ->where('entity_type_code = ?', CreateMerchantEntity::ENTITY_TYPE_CODE)
        );
        if (!$entityTypeId) {
            return;
        }

        // 2. Resolve the default attribute set id.
        $attributeSetId = (int) $connection->fetchOne(
            $connection->select()
                ->from($eavAttrSetTable, 'attribute_set_id')
                ->where('entity_type_id = ?', $entityTypeId)
                ->where('attribute_set_name = ?', CreateMerchantEntity::DEFAULT_ATTRIBUTE_SET_NAME)
        );
        if (!$attributeSetId) {
            return;
        }

        // 3. Build attribute_code => [attribute_id, backend_type].
        $rows = $connection->fetchAll(
            $connection->select()
                ->from($eavAttrTable, ['attribute_id', 'attribute_code', 'backend_type'])
                ->where('entity_type_id = ?', $entityTypeId)
        );
        $attrMap = [];
        foreach ($rows as $r) {
            $attrMap[$r['attribute_code']] = [
                'id'           => (int) $r['attribute_id'],
                'backend_type' => (string) $r['backend_type'],
            ];
        }
        if (!isset($attrMap[MerchantInterface::MERCHANT_CODE])) {
            return;
        }

        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        foreach (self::sampleData() as $row) {
            $merchantCode = (string) $row[MerchantInterface::MERCHANT_CODE];

            // Idempotency: skip if this merchant_code is already present.
            $existing = $connection->fetchOne(
                $connection->select()
                    ->from($varcharTable, 'entity_id')
                    ->where('attribute_id = ?', $attrMap[MerchantInterface::MERCHANT_CODE]['id'])
                    ->where('value = ?', $merchantCode)
                    ->limit(1)
            );
            if ($existing) {
                continue;
            }

            // Entity row
            $connection->insert($entityTable, [
                'entity_type_id'   => $entityTypeId,
                'attribute_set_id' => $attributeSetId,
                'store_id'         => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
            $entityId = (int) $connection->lastInsertId($entityTable);

            // Value rows
            foreach ($row as $code => $value) {
                if (!isset($attrMap[$code])) {
                    continue;
                }
                if ($value === null || $value === '') {
                    continue;
                }
                $backendType = $attrMap[$code]['backend_type'];
                if ($backendType === 'static') {
                    continue;
                }
                $valueTable = $setup->getTable('magenest_merchant_entity_' . $backendType);

                $connection->insert($valueTable, [
                    'attribute_id' => $attrMap[$code]['id'],
                    'store_id'     => 0,
                    'entity_id'    => $entityId,
                    'value'        => $value,
                ]);
            }
        }
    }

    /**
     * Canonical sample rows.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function sampleData(): array
    {
        $hanoi    = City::HA_NOI;
        $hcm      = City::HCM;
        $danang   = City::DA_NANG;
        $haiphong = City::HAI_PHONG;

        return [
            [
                MerchantInterface::MERCHANT_CODE      => '00181602',
                MerchantInterface::MC_PHONE           => '00181602112',
                MerchantInterface::STORE_NAME         => 'Smile Store Hà Nội',
                MerchantInterface::ACTIVE_DATE        => '2026-04-24 13:57:00',
                MerchantInterface::ONBOARDING_DATE    => '2026-04-20 10:00:00',
                MerchantInterface::LATEST_UPDATE_DATE => '2026-05-10 13:57:00',
                MerchantInterface::MERCHANT_STATUS    => Status::ACTIVE,
                MerchantInterface::KYC_LEVEL          => 'Level 3',
                MerchantInterface::MERCHANT_TYPE      => 'Big Merchant',
                MerchantInterface::PARTNER            => 'FEC',
                MerchantInterface::DSA_PHONE          => '0375647894',
                MerchantInterface::CITY               => $hanoi,
                MerchantInterface::DISTRICT           => 101,
                MerchantInterface::WARD               => 10101,
            ],
            [
                MerchantInterface::MERCHANT_CODE      => '00181601',
                MerchantInterface::MC_PHONE           => '00181601321',
                MerchantInterface::STORE_NAME         => 'Smile Store Hai Bà Trưng',
                MerchantInterface::ACTIVE_DATE        => null,
                MerchantInterface::ONBOARDING_DATE    => '2026-04-25 09:00:00',
                MerchantInterface::LATEST_UPDATE_DATE => '2026-04-30 14:11:00',
                MerchantInterface::MERCHANT_STATUS    => Status::REJECTED,
                MerchantInterface::KYC_LEVEL          => 'Level 3',
                MerchantInterface::MERCHANT_TYPE      => 'Big Merchant',
                MerchantInterface::PARTNER            => 'FEC',
                MerchantInterface::DSA_PHONE          => '0375647894',
                MerchantInterface::CITY               => $hanoi,
                MerchantInterface::DISTRICT           => 101,
                MerchantInterface::WARD               => 10102,
            ],
            [
                MerchantInterface::MERCHANT_CODE      => '00181600',
                MerchantInterface::MC_PHONE           => '00181600777',
                MerchantInterface::STORE_NAME         => 'Smile Store Hoàn Kiếm',
                MerchantInterface::ACTIVE_DATE        => null,
                MerchantInterface::ONBOARDING_DATE    => '2026-04-22 09:00:00',
                MerchantInterface::LATEST_UPDATE_DATE => '2026-04-24 14:19:00',
                MerchantInterface::MERCHANT_STATUS    => Status::PENDING,
                MerchantInterface::KYC_LEVEL          => 'Level 3',
                MerchantInterface::MERCHANT_TYPE      => 'Big Merchant',
                MerchantInterface::PARTNER            => 'FEC',
                MerchantInterface::DSA_PHONE          => '0375647894',
                MerchantInterface::CITY               => $hanoi,
                MerchantInterface::DISTRICT           => 102,
                MerchantInterface::WARD               => 10201,
            ],
            [
                MerchantInterface::MERCHANT_CODE      => '00181599',
                MerchantInterface::MC_PHONE           => '00181599222',
                MerchantInterface::STORE_NAME         => 'Smile Store Quận 1',
                MerchantInterface::ACTIVE_DATE        => null,
                MerchantInterface::ONBOARDING_DATE    => '2026-07-30 11:00:00',
                MerchantInterface::LATEST_UPDATE_DATE => '2026-08-01 11:31:00',
                MerchantInterface::MERCHANT_STATUS    => Status::PENDING,
                MerchantInterface::KYC_LEVEL          => 'Level 3',
                MerchantInterface::MERCHANT_TYPE      => 'Big Merchant',
                MerchantInterface::PARTNER            => 'FEC',
                MerchantInterface::DSA_PHONE          => '0375647894',
                MerchantInterface::CITY               => $hcm,
                MerchantInterface::DISTRICT           => 201,
                MerchantInterface::WARD               => 20101,
            ],
            [
                MerchantInterface::MERCHANT_CODE      => '00121437',
                MerchantInterface::MC_PHONE           => '0367344734',
                MerchantInterface::STORE_NAME         => 'Smile Store Đống Đa',
                MerchantInterface::ACTIVE_DATE        => '2026-04-22 14:17:00',
                MerchantInterface::ONBOARDING_DATE    => '2026-04-18 14:00:00',
                MerchantInterface::LATEST_UPDATE_DATE => '2026-04-22 14:50:00',
                MerchantInterface::MERCHANT_STATUS    => Status::BLOCKED,
                MerchantInterface::KYC_LEVEL          => 'Level 3',
                MerchantInterface::MERCHANT_TYPE      => 'Big Merchant',
                MerchantInterface::PARTNER            => 'Smollan',
                MerchantInterface::DSA_PHONE          => '0375647894',
                MerchantInterface::CITY               => $hanoi,
                MerchantInterface::DISTRICT           => 103,
                MerchantInterface::WARD               => 10101,
            ],
            [
                MerchantInterface::MERCHANT_CODE      => '00108427',
                MerchantInterface::MC_PHONE           => '0367221138',
                MerchantInterface::STORE_NAME         => 'Smile Store Cầu Giấy',
                MerchantInterface::ACTIVE_DATE        => '2026-04-19 13:42:00',
                MerchantInterface::ONBOARDING_DATE    => '2026-04-15 09:00:00',
                MerchantInterface::LATEST_UPDATE_DATE => '2026-04-25 08:52:00',
                MerchantInterface::MERCHANT_STATUS    => Status::ACTIVE,
                MerchantInterface::KYC_LEVEL          => 'Level 2',
                MerchantInterface::MERCHANT_TYPE      => 'Small Merchant',
                MerchantInterface::PARTNER            => 'FEC',
                MerchantInterface::DSA_PHONE          => '0375647894',
                MerchantInterface::CITY               => $hanoi,
                MerchantInterface::DISTRICT           => 104,
                MerchantInterface::WARD               => 10102,
            ],
            [
                MerchantInterface::MERCHANT_CODE      => '00098451',
                MerchantInterface::MC_PHONE           => '0901234567',
                MerchantInterface::STORE_NAME         => 'TechWorld Đà Nẵng',
                MerchantInterface::ACTIVE_DATE        => '2026-03-10 10:00:00',
                MerchantInterface::ONBOARDING_DATE    => '2026-03-05 09:30:00',
                MerchantInterface::LATEST_UPDATE_DATE => '2026-05-01 16:25:00',
                MerchantInterface::MERCHANT_STATUS    => Status::ACTIVE,
                MerchantInterface::KYC_LEVEL          => 'Level 2',
                MerchantInterface::MERCHANT_TYPE      => 'Small Merchant',
                MerchantInterface::PARTNER            => 'FEC',
                MerchantInterface::DSA_PHONE          => '0399887766',
                MerchantInterface::CITY               => $danang,
                MerchantInterface::DISTRICT           => 301,
                MerchantInterface::WARD               => null,
            ],
            [
                MerchantInterface::MERCHANT_CODE      => '00098450',
                MerchantInterface::MC_PHONE           => '0905555111',
                MerchantInterface::STORE_NAME         => 'Fresh Mart Hải Phòng',
                MerchantInterface::ACTIVE_DATE        => null,
                MerchantInterface::ONBOARDING_DATE    => '2026-03-12 11:00:00',
                MerchantInterface::LATEST_UPDATE_DATE => '2026-04-02 09:15:00',
                MerchantInterface::MERCHANT_STATUS    => Status::PENDING,
                MerchantInterface::KYC_LEVEL          => 'Level 1',
                MerchantInterface::MERCHANT_TYPE      => 'Small Merchant',
                MerchantInterface::PARTNER            => 'Smollan',
                MerchantInterface::DSA_PHONE          => '0388776655',
                MerchantInterface::CITY               => $haiphong,
                MerchantInterface::DISTRICT           => null,
                MerchantInterface::WARD               => null,
            ],
            [
                MerchantInterface::MERCHANT_CODE      => '00075321',
                MerchantInterface::MC_PHONE           => '0908123456',
                MerchantInterface::STORE_NAME         => 'GreenLife Quận 7',
                MerchantInterface::ACTIVE_DATE        => '2026-02-14 09:00:00',
                MerchantInterface::ONBOARDING_DATE    => '2026-02-10 08:00:00',
                MerchantInterface::LATEST_UPDATE_DATE => '2026-05-12 10:30:00',
                MerchantInterface::MERCHANT_STATUS    => Status::ACTIVE,
                MerchantInterface::KYC_LEVEL          => 'Level 3',
                MerchantInterface::MERCHANT_TYPE      => 'Big Merchant',
                MerchantInterface::PARTNER            => 'FEC',
                MerchantInterface::DSA_PHONE          => '0377111222',
                MerchantInterface::CITY               => $hcm,
                MerchantInterface::DISTRICT           => 203,
                MerchantInterface::WARD               => 20301,
            ],
            [
                MerchantInterface::MERCHANT_CODE      => '00065421',
                MerchantInterface::MC_PHONE           => '0989654321',
                MerchantInterface::STORE_NAME         => 'Cozy Home Bình Thạnh',
                MerchantInterface::ACTIVE_DATE        => '2026-01-08 14:30:00',
                MerchantInterface::ONBOARDING_DATE    => '2026-01-05 10:00:00',
                MerchantInterface::LATEST_UPDATE_DATE => '2026-05-11 18:00:00',
                MerchantInterface::MERCHANT_STATUS    => Status::BLOCKED,
                MerchantInterface::KYC_LEVEL          => 'Level 2',
                MerchantInterface::MERCHANT_TYPE      => 'Small Merchant',
                MerchantInterface::PARTNER            => 'Smollan',
                MerchantInterface::DSA_PHONE          => '0366222111',
                MerchantInterface::CITY               => $hcm,
                MerchantInterface::DISTRICT           => 204,
                MerchantInterface::WARD               => null,
            ],
        ];
    }

    public static function getDependencies(): array
    {
        return [
            AddMerchantAttributes::class,
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
