<?php
declare(strict_types=1);

namespace Magenest\CustomBreadcrumbs\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddUseAsMainBreadcrumbAttribute implements DataPatchInterface
{
    public const ATTRIBUTE_CODE = 'use_as_main_breadcrumb';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CategorySetupFactory $categorySetupFactory
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $categorySetup->addAttribute(
            Category::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'type'         => 'int',
                'label'        => 'Use as Main Breadcrumb',
                'input'        => 'boolean',
                'source'       => Boolean::class,
                'global'       => ScopedAttributeInterface::SCOPE_STORE,
                'required'     => false,
                'default'      => '0',
                'sort_order'   => 100,
                'user_defined' => true,
                'visible'      => true,
                'group'        => 'General Information',
                'note'         => 'If Yes, PDP breadcrumbs may stop at this category.',
            ]
        );

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
