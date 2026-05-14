<?php
/**
 * app/code/Magenest/Merchant/Setup/Patch/Data/RepairSampleMerchants.php
 *
 * Removes orphan rows (entity_type_id = 0) left by the old broken seed,
 * then re-runs the raw-SQL seeder. Safe to re-run.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class RepairSampleMerchants implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        $connection  = $this->moduleDataSetup->getConnection();
        $entityTable = $this->moduleDataSetup->getTable('magenest_merchant_entity');

        // Drop corrupt rows — FK ON DELETE CASCADE removes their value-table rows.
        $connection->delete($entityTable, ['entity_type_id = 0']);

        // Re-seed using the canonical raw-SQL inserter.
        AddSampleMerchants::seed($this->moduleDataSetup);

        $this->moduleDataSetup->endSetup();
        return $this;
    }

    public static function getDependencies(): array
    {
        return [
            AddSampleMerchants::class,
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
