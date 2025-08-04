<?php
declare(strict_types=1);

namespace Magenest\BusinessDirectory\Model;

use Magento\Framework\Model\AbstractModel;
use Magenest\BusinessDirectory\Api\Data\DirectoryInterface;
use Magenest\BusinessDirectory\Model\ResourceModel\Directory as ResourceModel;

class Directory extends AbstractModel implements DirectoryInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'business_directory';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'business_directory';

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    public function getCustomerDirectory(): string
    {
        return (string)$this->getData(self::CUSTOMER_DIRECTORY);
    }

    public function setCustomerDirectory(string $directory): self
    {
        return $this->setData(self::CUSTOMER_DIRECTORY, $directory);
    }

    public function getDescription(): ?string
    {
        return $this->getData(self::DESCRIPTION);
    }

    public function setDescription(?string $description): self
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    public function getConsumerFrontend(): bool
    {
        return (bool)$this->getData(self::CONSUMER_FRONTEND);
    }

    public function setConsumerFrontend(bool $value): self
    {
        return $this->setData(self::CONSUMER_FRONTEND, $value);
    }

    public function getBusinessFrontend(): bool
    {
        return (bool)$this->getData(self::BUSINESS_FRONTEND);
    }

    public function setBusinessFrontend(bool $value): self
    {
        return $this->setData(self::BUSINESS_FRONTEND, $value);
    }

    public function getNotes(): ?string
    {
        return $this->getData(self::NOTES);
    }

    public function setNotes(?string $notes): self
    {
        return $this->setData(self::NOTES, $notes);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
