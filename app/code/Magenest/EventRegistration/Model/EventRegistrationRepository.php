<?php
declare(strict_types=1);

namespace Magenest\EventRegistration\Model;

use Magenest\EventRegistration\Api\Data\EventRegistrationInterface;
use Magenest\EventRegistration\Api\EventRegistrationRepositoryInterface;
use Magenest\EventRegistration\Model\ResourceModel\EventRegistration as RegistrationResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class EventRegistrationRepository implements EventRegistrationRepositoryInterface
{
    public function __construct(
        private readonly RegistrationResource $resource,
        private readonly EventRegistrationFactory $registrationFactory
    ) {
    }

    public function save(EventRegistrationInterface $registration): EventRegistrationInterface
    {
        try {
            $this->resource->save($registration);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(__('Could not save the registration: %1', $e->getMessage()), $e);
        }
        return $registration;
    }

    public function getById(int $id): EventRegistrationInterface
    {
        $registration = $this->registrationFactory->create();
        $this->resource->load($registration, $id);
        if (!$registration->getEntityId()) {
            throw new NoSuchEntityException(__('Registration "%1" does not exist.', $id));
        }
        return $registration;
    }
}
