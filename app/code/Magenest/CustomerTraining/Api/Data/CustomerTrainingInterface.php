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

    public function getEntityId(): ?int;

    public function setEntityId(int $entityId): self;

    public function getFirstName(): ?string;

    public function setFirstName(string $firstName): self;

    public function getLastName(): ?string;

    public function setLastName(string $lastName): self;

    public function getAddress(): ?string;

    public function setAddress(?string $address): self;

    public function getCity(): ?string;

    public function setCity(?string $city): self;

    public function getAge(): ?int;

    public function setAge(?int $age): self;

    public function getCreatedAt(): ?string;

    public function getUpdatedAt(): ?string;
}
