<?php
/**
 * app/code/Magenest/Merchant/Plugin/InvalidateCatalogSearchOnMerchantSave.php
 *
 * Mirrors Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Save: when a
 * single merchant's store_name changes, reindex ONLY the products that
 * reference that merchant_id — never touch the rest of the catalog.
 *
 * Flow
 *  - beforeSave: snapshot the current `store_name` from DB (the model's
 *    origData has already been refreshed by EAV save by the time afterSave
 *    runs, so we can't rely on dataHasChangedFor()).
 *  - afterSave : if the name actually changed, find product IDs whose
 *    `merchant` attribute equals this merchant id, then call
 *    `$indexer->reindexList($productIds)` to update just those ES rows.
 *
 *  Update-on-Schedule mode is also respected: reindexList works in both
 *  modes — for scheduled indexers Magento's MView won't auto-pick our
 *  change (we don't touch `catalog_product_entity_*`), so an explicit
 *  reindex of the affected rows is the only correct option.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Plugin;

use Magenest\Merchant\Api\Data\MerchantInterface;
use Magenest\Merchant\Api\MerchantRepositoryInterface;
use Magenest\Merchant\Setup\Patch\Data\AddMerchantProductAttribute;
use Magenest\Merchant\Setup\Patch\Data\CreateMerchantEntity;
use Magento\Catalog\Model\Product;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerRegistry;

class InvalidateCatalogSearchOnMerchantSave
{
    /** @var array<int,string|null> */
    private array $previousStoreName = [];

    private ?int $merchantProductAttributeId = null;

    private ?int $merchantStoreNameAttributeId = null;

    public function __construct(
        private readonly IndexerRegistry $indexerRegistry,
        private readonly ResourceConnection $resourceConnection,
        private readonly EavConfig $eavConfig
    ) {
    }

    /**
     * @param MerchantRepositoryInterface $subject
     * @param MerchantInterface $merchant
     * @return array{0: MerchantInterface}
     */
    public function beforeSave(
        MerchantRepositoryInterface $subject,
        MerchantInterface $merchant
    ): array {
        $id = (int) $merchant->getId();
        if ($id) {
            // Snapshot BEFORE save commits — origData on the model is overwritten
            // by EAV's _afterLoad/setOrigData during/after save.
            $this->previousStoreName[$id] = $this->fetchCurrentStoreName($id);
        }
        return [$merchant];
    }

    public function afterSave(
        MerchantRepositoryInterface $subject,
        MerchantInterface $result,
        MerchantInterface $merchant
    ): MerchantInterface {
        $id = (int) $result->getId();
        if (!$id) {
            return $result;
        }

        $newName = (string) $result->getData(MerchantInterface::STORE_NAME);
        $oldName = $this->previousStoreName[$id] ?? null;
        unset($this->previousStoreName[$id]);

        // New merchant (no previous value) or no change → nothing to reindex.
        if ($oldName === null || $oldName === $newName) {
            return $result;
        }

        $productIds = $this->findProductsForMerchant($id);
        if (!$productIds) {
            return $result;
        }

        // Targeted reindex — only the rows that actually reference this merchant.
        $this->indexerRegistry->get(Fulltext::INDEXER_ID)->reindexList($productIds);

        return $result;
    }

    /**
     * Read the currently-persisted store_name for a merchant directly from DB.
     */
    private function fetchCurrentStoreName(int $merchantId): ?string
    {
        $attrId = $this->getMerchantStoreNameAttributeId();
        if (!$attrId) {
            return null;
        }
        $connection = $this->resourceConnection->getConnection();
        $value = $connection->fetchOne(
            $connection->select()
                ->from(
                    $this->resourceConnection->getTableName('magenest_merchant_entity_varchar'),
                    'value'
                )
                ->where('entity_id = ?', $merchantId)
                ->where('attribute_id = ?', $attrId)
                ->where('store_id = ?', 0)
                ->limit(1)
        );
        return $value === false ? null : (string) $value;
    }

    private function getMerchantStoreNameAttributeId(): ?int
    {
        if ($this->merchantStoreNameAttributeId !== null) {
            return $this->merchantStoreNameAttributeId ?: null;
        }
        $connection = $this->resourceConnection->getConnection();
        $entityTypeId = (int) $connection->fetchOne(
            $connection->select()
                ->from($this->resourceConnection->getTableName('eav_entity_type'), 'entity_type_id')
                ->where('entity_type_code = ?', CreateMerchantEntity::ENTITY_TYPE_CODE)
        );
        if (!$entityTypeId) {
            return $this->merchantStoreNameAttributeId = 0 ? null : null;
        }
        $this->merchantStoreNameAttributeId = (int) $connection->fetchOne(
            $connection->select()
                ->from($this->resourceConnection->getTableName('eav_attribute'), 'attribute_id')
                ->where('entity_type_id = ?', $entityTypeId)
                ->where('attribute_code = ?', MerchantInterface::STORE_NAME)
        );
        return $this->merchantStoreNameAttributeId ?: null;
    }

    /**
     * @return int[]
     */
    private function findProductsForMerchant(int $merchantId): array
    {
        if ($this->merchantProductAttributeId === null) {
            $attribute = $this->eavConfig->getAttribute(
                Product::ENTITY,
                AddMerchantProductAttribute::ATTRIBUTE_CODE
            );
            $this->merchantProductAttributeId = (int) $attribute->getId();
        }
        if (!$this->merchantProductAttributeId) {
            return [];
        }

        $connection = $this->resourceConnection->getConnection();
        $rows = $connection->fetchCol(
            $connection->select()
                ->from(
                    $this->resourceConnection->getTableName('catalog_product_entity_int'),
                    'entity_id'
                )
                ->where('attribute_id = ?', $this->merchantProductAttributeId)
                ->where('value = ?', $merchantId)
        );
        return array_map('intval', $rows);
    }
}
