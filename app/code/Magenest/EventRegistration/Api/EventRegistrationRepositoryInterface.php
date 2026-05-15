<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Api;

use Magenest\EventRegistration\Api\Data\EventRegistrationInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface EventRegistrationRepositoryInterface
{
    public function save(EventRegistrationInterface $registration): EventRegistrationInterface;

    public function getById(int $id): EventRegistrationInterface;
}
