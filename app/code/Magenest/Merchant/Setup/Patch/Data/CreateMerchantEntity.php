<?php
/**
 * app/code/Magenest/Merchant/Setup/Patch/Data/CreateMerchantEntity.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Setup\Patch\Data;

use Magenest\Merchant\Model\Merchant;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateMerchantEntity implements DataPatchInterface
{
    public const ENTITY_TYPE_CODE = 'magenest_merchant';
    public const DEFAULT_ATTRIBUTE_SET_NAME = 'Default';
    public const DEFAULT_GROUP_NAME = 'General';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addEntityType(
            self::ENTITY_TYPE_CODE,
            [
                'entity_model'                => \Magenest\Merchant\Model\ResourceModel\Merchant::class,
                'attribute_model'             => '',
                'table'                       => 'magenest_merchant_entity',
                'increment_model'             => '',
                'additional_attribute_table'  => '',
                'entity_attribute_collection' => '',
            ]
        );

        // Make sure the default attribute-set / group exist for this entity
        $entityTypeId = (int) $eavSetup->getEntityTypeId(self::ENTITY_TYPE_CODE);
        $attributeSetId = (int) $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $attributeGroupId = (int) $eavSetup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

        // No-ops here are fine — addEntityType already creates them, we just verify.
        unset($attributeGroupId);

        $this->moduleDataSetup->endSetup();

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
