<?php
/**
 * app/code/Magenest/CustomerTraining/Model/CustomerTraining.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Model;

use Magenest\CustomerTraining\Api\Data\CustomerTrainingInterface;
use Magenest\CustomerTraining\Model\ResourceModel\CustomerTraining as CustomerTrainingResource;
use Magento\Framework\Model\AbstractModel;

class CustomerTraining extends AbstractModel implements CustomerTrainingInterface
{
    protected $_eventPrefix = 'magenest_customer_training';

    protected function _construct(): void
    {
        $this->_init(CustomerTrainingResource::class);
    }

    public function getEntityId(): ?int
    {
        $id = $this->getData(self::ENTITY_ID);
        return $id !== null ? (int) $id : null;
    }

    public function setEntityId($entityId): self
    {
        return $this->setData(self::ENTITY_ID, (int) $entityId);
    }

    public function getFirstName(): ?string
    {
        $value = $this->getData(self::FIRST_NAME);
        return $value !== null ? (string) $value : null;
    }

    public function setFirstName(string $firstName): self
    {
        return $this->setData(self::FIRST_NAME, $firstName);
    }

    public function getLastName(): ?string
    {
        $value = $this->getData(self::LAST_NAME);
        return $value !== null ? (string) $value : null;
    }

    public function setLastName(string $lastName): self
    {
        return $this->setData(self::LAST_NAME, $lastName);
    }

    public function getAddress(): ?string
    {
        $value = $this->getData(self::ADDRESS);
        return $value !== null ? (string) $value : null;
    }

    public function setAddress(?string $address): self
    {
        return $this->setData(self::ADDRESS, $address);
    }

    public function getCity(): ?string
    {
        $value = $this->getData(self::CITY);
        return $value !== null ? (string) $value : null;
    }

    public function setCity(?string $city): self
    {
        return $this->setData(self::CITY, $city);
    }

    public function getAge(): ?int
    {
        $value = $this->getData(self::AGE);
        return $value !== null ? (int) $value : null;
    }

    public function setAge(?int $age): self
    {
        return $this->setData(self::AGE, $age);
    }

    public function getCreatedAt(): ?string
    {
        $value = $this->getData(self::CREATED_AT);
        return $value !== null ? (string) $value : null;
    }

    public function getUpdatedAt(): ?string
    {
        $value = $this->getData(self::UPDATED_AT);
        return $value !== null ? (string) $value : null;
    }
}
