<?php
/**
 * app/code/Magenest/CustomerTraining/Api/Data/CustomerTrainingInterface.php
 */
declare(strict_types=1);

namespace Magenest\CustomerTraining\Api\Data;

interface CustomerTrainingInterface
{
    public const ENTITY_ID  = 'entity_id';
    public const FIRST_NAME = 'first_name';
    public const LAST_NAME  = 'last_name';
    public const ADDRESS    = 'address';
    public const CITY       = 'city';
    public const AGE        = 'age';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * Get entity ID.
     *
     * @return int|null
     */
    public function getEntityId(): ?int;

    /**
     * Set entity ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId(int $entityId): self;

    /**
     * Get first name.
     *
     * @return string|null
     */
    public function getFirstName(): ?string;

    /**
     * Set first name.
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstName(string $firstName): self;

    /**
     * Get last name.
     *
     * @return string|null
     */
    public function getLastName(): ?string;

    /**
     * Set last name.
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastName(string $lastName): self;

    /**
     * Get address.
     *
     * @return string|null
     */
    public function getAddress(): ?string;

    /**
     * Set address.
     *
     * @param string|null $address
     * @return $this
     */
    public function setAddress(?string $address): self;

    /**
     * Get city.
     *
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * Set city.
     *
     * @param string|null $city
     * @return $this
     */
    public function setCity(?string $city): self;

    /**
     * Get age.
     *
     * @return int|null
     */
    public function getAge(): ?int;

    /**
     * Set age.
     *
     * @param int|null $age
     * @return $this
     */
    public function setAge(?int $age): self;

    /**
     * Get creation timestamp.
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Get last update timestamp.
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;
}
