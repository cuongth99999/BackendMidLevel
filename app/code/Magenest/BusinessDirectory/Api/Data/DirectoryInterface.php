<?php
declare(strict_types=1);

namespace Magenest\BusinessDirectory\Api\Data;

interface DirectoryInterface
{
    const ENTITY_ID = 'entity_id';
    const CUSTOMER_DIRECTORY = 'customer_directory';
    const DESCRIPTION = 'description';
    const CONSUMER_FRONTEND = 'consumer_frontend';
    const BUSINESS_FRONTEND = 'business_frontend';
    const NOTES = 'notes';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    /**
     * @return int|null
     */
    public function getId();
    
    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);
    
    /**
     * @return string
     */
    public function getCustomerDirectory(): string;
    
    /**
     * @param string $directory
     * @return $this
     */
    public function setCustomerDirectory(string $directory): self;
    
    /**
     * @return string|null
     */
    public function getDescription(): ?string;
    
    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description): self;
    
    /**
     * @return bool
     */
    public function getConsumerFrontend(): bool;
    
    /**
     * @param bool $value
     * @return $this
     */
    public function setConsumerFrontend(bool $value): self;
    
    /**
     * @return bool
     */
    public function getBusinessFrontend(): bool;
    
    /**
     * @param bool $value
     * @return $this
     */
    public function setBusinessFrontend(bool $value): self;
    
    /**
     * @return string|null
     */
    public function getNotes(): ?string;
    
    /**
     * @param string|null $notes
     * @return $this
     */
    public function setNotes(?string $notes): self;
}