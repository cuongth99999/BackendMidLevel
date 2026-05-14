<?php
/**
 * app/code/Magenest/Merchant/Model/ResourceModel/Merchant.php
 *
 * EAV resource model. Declares the entity-type code for the AbstractEntity
 * machinery and force-sets `entity_type_id` + `attribute_set_id` in
 * _beforeSave so new records never get persisted with a 0/0 pair.
 *
 * The override is necessary because:
 *   - AbstractEntity::save() only auto-sets entity_type_id when its
 *     getTypeId() returns a non-zero value, which is not always the case
 *     in our setup (entity-type model is sometimes resolved without an ID).
 *   - attribute_set_id is never auto-set by AbstractEntity at all.
 *   - eav_entity_type.default_attribute_set_id is left at 0 by EavSetup's
 *     addEntityType(), so we have to look the default set up by name.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Model\ResourceModel;

use Magenest\Merchant\Setup\Patch\Data\CreateMerchantEntity;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\DataObject;

class Merchant extends AbstractEntity
{
    /** @var int|null */
    private $cachedAttributeSetId;

    protected function _construct(): void
    {
        $this->setType(CreateMerchantEntity::ENTITY_TYPE_CODE);
        $this->setConnection('default');
    }

    public function getEntityTable(): string
    {
        return $this->getTable('magenest_merchant_entity');
    }

    protected function _beforeSave(DataObject $object): self
    {
        // entity_type_id — fall back to the type id we hold; if even that is
        // empty (e.g. EavConfig state lag) resolve straight from the table.
        if (!$object->getData('entity_type_id')) {
            $entityTypeId = (int) $this->getTypeId();
            if (!$entityTypeId) {
                $entityTypeId = (int) $this->getConnection()->fetchOne(
                    $this->getConnection()->select()
                        ->from($this->getTable('eav_entity_type'), 'entity_type_id')
                        ->where('entity_type_code = ?', CreateMerchantEntity::ENTITY_TYPE_CODE)
                );
            }
            $object->setData('entity_type_id', $entityTypeId);
        }

        // attribute_set_id — default set, looked up by name once and cached.
        if (!$object->getData('attribute_set_id')) {
            $object->setData('attribute_set_id', $this->resolveDefaultAttributeSetId());
        }

        return parent::_beforeSave($object);
    }

    private function resolveDefaultAttributeSetId(): int
    {
        if ($this->cachedAttributeSetId !== null) {
            return $this->cachedAttributeSetId;
        }

        $entityTypeId = (int) $this->getTypeId();
        $defaultFromType = (int) $this->getEntityType()->getDefaultAttributeSetId();

        if ($defaultFromType) {
            return $this->cachedAttributeSetId = $defaultFromType;
        }

        $this->cachedAttributeSetId = (int) $this->getConnection()->fetchOne(
            $this->getConnection()->select()
                ->from($this->getTable('eav_attribute_set'), 'attribute_set_id')
                ->where('entity_type_id = ?', $entityTypeId)
                ->where('attribute_set_name = ?', CreateMerchantEntity::DEFAULT_ATTRIBUTE_SET_NAME)
                ->limit(1)
        );

        return $this->cachedAttributeSetId;
    }
}
