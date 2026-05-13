<?php
/**
 * app/code/Magenest/CustomerTraining/Setup/Patch/Schema/CreateCustomerTrainingTable.php
 *
 * The `customer_training` table lives in the SEPARATE `well_trained` database.
 * Declarative schema (etc/db_schema.xml) cannot target a custom resource — its
 * XSD restricts `resource` to {default, checkout, sales} — so we issue DDL via
 * a Schema Patch directly against the `well_trained` connection.
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Setup\Patch\Schema;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class CreateCustomerTrainingTable implements SchemaPatchInterface, PatchRevertableInterface
{
    public const TABLE_NAME      = 'customer_training';
    public const CONNECTION_NAME = 'well_trained';

    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    public function apply(): self
    {
        $connection = $this->resourceConnection->getConnection(self::CONNECTION_NAME);

        if ($connection->isTableExists(self::TABLE_NAME)) {
            return $this;
        }

        $table = $connection->newTable(self::TABLE_NAME)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'first_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'First Name'
            )
            ->addColumn(
                'last_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Last Name'
            )
            ->addColumn(
                'address',
                Table::TYPE_TEXT,
                500,
                ['nullable' => true],
                'Address'
            )
            ->addColumn(
                'city',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'City'
            )
            ->addColumn(
                'age',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Age'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )
            ->addIndex(
                $connection->getIndexName(self::TABLE_NAME, ['last_name']),
                ['last_name']
            )
            ->addIndex(
                $connection->getIndexName(self::TABLE_NAME, ['city']),
                ['city']
            )
            ->setComment('Customer Training (well_trained database)');

        $connection->createTable($table);

        return $this;
    }

    public function revert(): void
    {
        $connection = $this->resourceConnection->getConnection(self::CONNECTION_NAME);
        if ($connection->isTableExists(self::TABLE_NAME)) {
            $connection->dropTable(self::TABLE_NAME);
        }
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
