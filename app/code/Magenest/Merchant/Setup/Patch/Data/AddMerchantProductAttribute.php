<?php
/**
 * app/code/Magenest/Merchant/Setup/Patch/Data/AddMerchantProductAttribute.php
 *
 * Adds a `merchant` select attribute to the catalog_product entity.
 *
 * Stored value is the Magenest_Merchant entity_id (int). The source model
 * returns store_name as the option label, so the catalogsearch / Elasticsearch
 * fulltext indexer ends up indexing the store_name on each product that has
 * a merchant assigned — that's what makes "search products by merchant store
 * name" work on the storefront.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Setup\Patch\Data;

use Magenest\Merchant\Model\Source\Product\MerchantOptions;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddMerchantProductAttribute implements DataPatchInterface
{
    public const ATTRIBUTE_CODE = 'merchant';

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

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'type'                       => 'int',
                'label'                      => 'Merchant',
                'input'                      => 'select',
                'source'                     => MerchantOptions::class,
                'global'                     => ScopedAttributeInterface::SCOPE_GLOBAL,
                'required'                   => false,
                'user_defined'               => true,
                'visible'                    => true,
                'searchable'                 => true,   // is_searchable=1 → ES picks it up
                'filterable'                 => true,   // layered nav
                'filterable_in_search'       => true,
                'visible_in_advanced_search' => true,
                'comparable'                 => false,
                'visible_on_front'           => true,
                'used_in_product_listing'    => true,
                'used_for_sort_by'           => false,
                'unique'                     => false,
                'apply_to'                   => '',
                'group'                      => 'General',
                'sort_order'                 => 200,
                'is_used_in_grid'            => true,
                'is_visible_in_grid'         => true,
                'is_filterable_in_grid'      => true,
                'search_weight'              => 5,      // boost merchant matches
            ]
        );

        $this->moduleDataSetup->endSetup();
        return $this;
    }

    public static function getDependencies(): array
    {
        // Need the merchant EAV entity and rows in place so the source model
        // returns meaningful labels at index time.
        return [
            AddMerchantAttributes::class,
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
