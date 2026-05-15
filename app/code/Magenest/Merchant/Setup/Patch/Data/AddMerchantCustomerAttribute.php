<?php
/**
 * app/code/Magenest/Merchant/Setup/Patch/Data/AddMerchantCustomerAttribute.php
 *
 * Adds a `merchant_id` customer attribute (select, source = merchant list).
 *
 * `used_in_forms = ['adminhtml_customer']` so the admin customer edit
 * screen renders the dropdown. Frontend forms are NOT included — this is
 * an admin-managed referral attribute, not customer-facing.
 *
 * Stored value lives in customer_entity_int (entity_id, attribute_id, value)
 * — that's where the Save controller writes via INSERT ... ON DUPLICATE KEY.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Setup\Patch\Data;

use Magenest\Merchant\Model\Source\Customer\MerchantOptions;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddMerchantCustomerAttribute implements DataPatchInterface
{
    public const ATTRIBUTE_CODE = 'merchant_id';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CustomerSetupFactory $customerSetupFactory,
        private readonly AttributeSetFactory $attributeSetFactory
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = (int) $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = (int) $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'type'             => 'int',
                'label'            => 'Merchant',
                'input'            => 'select',
                'source'           => MerchantOptions::class,
                'required'         => false,
                'visible'          => true,
                'user_defined'     => true,
                'system'           => false,
                'position'         => 200,
                'sort_order'       => 200,
                'is_used_in_grid'  => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => false,
            ]
        );

        // Make the attribute visible on the admin customer edit form and
        // register it on the default attribute set/group so eav-grid views
        // can include it.
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, self::ATTRIBUTE_CODE);
        $attribute->addData([
            'attribute_set_id'   => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms'      => ['adminhtml_customer'],
        ]);
        $attribute->save();

        $this->moduleDataSetup->endSetup();
        return $this;
    }

    public static function getDependencies(): array
    {
        // Merchant entity + rows must exist before the source model can
        // produce option labels at first render.
        return [
            AddSampleMerchants::class,
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
