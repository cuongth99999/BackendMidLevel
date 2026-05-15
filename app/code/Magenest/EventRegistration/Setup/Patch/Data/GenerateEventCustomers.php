<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Setup\Patch\Data;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Seed ~3000 customers used by the mass-registration flow.
 * Idempotent: counts existing seed rows and only fills the gap.
 * Direct insert (skips events/observers) — acceptable for test data only.
 */
class GenerateEventCustomers implements DataPatchInterface
{
    private const TARGET_COUNT = 3000;
    private const EMAIL_PREFIX = 'evt_';
    private const EMAIL_DOMAIN = '@magenest.example';
    private const BATCH_SIZE   = 500;
    private const PASSWORD     = 'Password123!';

    public function __construct(
        private readonly ModuleDataSetupInterface $setup,
        private readonly EncryptorInterface $encryptor,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger
    ) {
    }

    public function apply()
    {
        $connection = $this->setup->getConnection();
        $table      = $this->setup->getTable('customer_entity');

        $existing = (int) $connection->fetchOne(
            "SELECT COUNT(*) FROM {$table} WHERE email LIKE :prefix",
            ['prefix' => self::EMAIL_PREFIX . '%' . self::EMAIL_DOMAIN]
        );
        if ($existing >= self::TARGET_COUNT) {
            return $this;
        }

        $store     = $this->storeManager->getStore();
        $website   = $this->storeManager->getWebsite();
        $storeId   = (int) $store->getId();
        $websiteId = (int) $website->getId();
        $storeName = (string) $store->getName();
        $hash      = $this->encryptor->getHash(self::PASSWORD, true);
        $now       = date('Y-m-d H:i:s');

        $needed = self::TARGET_COUNT - $existing;
        $rows   = [];
        $startIndex = $existing + 1;

        for ($i = 0; $i < $needed; $i++) {
            $idx = $startIndex + $i;
            $rows[] = [
                'website_id'    => $websiteId,
                'email'         => sprintf('%s%05d%s', self::EMAIL_PREFIX, $idx, self::EMAIL_DOMAIN),
                'group_id'      => 1,
                'store_id'      => $storeId,
                'created_in'    => $storeName,
                'firstname'     => 'Event',
                'lastname'      => 'Tester' . $idx,
                'password_hash' => $hash,
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];

            if (count($rows) >= self::BATCH_SIZE) {
                $connection->insertMultiple($table, $rows);
                $rows = [];
            }
        }
        if ($rows) {
            $connection->insertMultiple($table, $rows);
        }

        $this->logger->info(sprintf(
            '[Magenest_EventRegistration] Seeded %d customers (target=%d, existing=%d).',
            $needed,
            self::TARGET_COUNT,
            $existing
        ));

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
