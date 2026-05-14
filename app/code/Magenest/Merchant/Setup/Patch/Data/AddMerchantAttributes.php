<?php
/**
 * app/code/Magenest/Merchant/Setup/Patch/Data/AddMerchantAttributes.php
 */
declare(strict_types=1);

namespace Magenest\Merchant\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddMerchantAttributes implements DataPatchInterface
{
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
        $entityType = CreateMerchantEntity::ENTITY_TYPE_CODE;

        $attributes = [
            'merchant_code' => [
                'label'        => 'Merchant ID',
                'type'         => 'varchar',
                'input'        => 'text',
                'required'     => true,
                'sort_order'   => 10,
            ],
            'mc_phone' => [
                'label'        => "MC's Phone No.",
                'type'         => 'varchar',
                'input'        => 'text',
                'required'     => false,
                'sort_order'   => 20,
            ],
            'store_name' => [
                'label'        => 'Store Name',
                'type'         => 'varchar',
                'input'        => 'text',
                'required'     => true,
                'sort_order'   => 30,
            ],
            'category_ids' => [
                'label'         => 'Category',
                'type'          => 'text',
                'input'         => 'multiselect',
                'source'        => \Magenest\Merchant\Model\Source\Category::class,
                'backend'       => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'required'      => false,
                'sort_order'    => 40,
            ],
            'active_date' => [
                'label'         => 'Active Date',
                'type'          => 'datetime',
                'input'         => 'date',
                'required'      => false,
                'sort_order'    => 50,
            ],
            'latest_update_date' => [
                'label'         => 'Latest Update Date',
                'type'          => 'datetime',
                'input'         => 'date',
                'required'      => false,
                'sort_order'    => 60,
            ],
            'onboarding_date' => [
                'label'         => 'Onboarding Date',
                'type'          => 'datetime',
                'input'         => 'date',
                'required'      => false,
                'sort_order'    => 70,
            ],
            'merchant_status' => [
                'label'         => 'Merchant Status',
                'type'          => 'int',
                'input'         => 'select',
                'source'        => \Magenest\Merchant\Model\Source\Status::class,
                'required'      => true,
                'sort_order'    => 80,
            ],
            'kyc_level' => [
                'label'        => 'KYC Level',
                'type'         => 'varchar',
                'input'        => 'text',
                'required'     => false,
                'sort_order'   => 90,
            ],
            'merchant_type' => [
                'label'        => 'Merchant Type',
                'type'         => 'varchar',
                'input'        => 'text',
                'required'     => false,
                'sort_order'   => 100,
            ],
            'partner' => [
                'label'        => 'Partner',
                'type'         => 'varchar',
                'input'        => 'text',
                'required'     => false,
                'sort_order'   => 110,
            ],
            'dsa_phone' => [
                'label'        => "DSA's Phone No.",
                'type'         => 'varchar',
                'input'        => 'text',
                'required'     => false,
                'sort_order'   => 120,
            ],
            'city' => [
                'label'         => 'City',
                'type'          => 'int',
                'input'         => 'select',
                'source'        => \Magenest\Merchant\Model\Source\City::class,
                'required'      => false,
                'sort_order'    => 130,
            ],
            'district' => [
                'label'         => 'District',
                'type'          => 'int',
                'input'         => 'select',
                'source'        => \Magenest\Merchant\Model\Source\District::class,
                'required'      => false,
                'sort_order'    => 140,
            ],
            'ward' => [
                'label'         => 'Ward',
                'type'          => 'int',
                'input'         => 'select',
                'source'        => \Magenest\Merchant\Model\Source\Ward::class,
                'required'      => false,
                'sort_order'    => 150,
            ],
        ];

        foreach ($attributes as $code => $cfg) {
            $eavSetup->addAttribute(
                $entityType,
                $code,
                [
                    'type'            => $cfg['type'],
                    'label'           => $cfg['label'],
                    'input'           => $cfg['input'],
                    'required'        => $cfg['required'],
                    'sort_order'      => $cfg['sort_order'],
                    'user_defined'    => true,
                    'visible'         => true,
                    'system'          => false,
                    'group'           => CreateMerchantEntity::DEFAULT_GROUP_NAME,
                    'source'          => $cfg['source']  ?? '',
                    'backend'         => $cfg['backend'] ?? '',
                    'global'          => 1,
                ]
            );
        }

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    public static function getDependencies(): array
    {
        return [
            CreateMerchantEntity::class,
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
