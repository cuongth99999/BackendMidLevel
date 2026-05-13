<?php
/**
 * app/code/Magenest/BookingSchedule/Block/Adminhtml/Schedule.php
 */
declare(strict_types=1);

namespace Magenest\BookingSchedule\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class Schedule extends Template
{
    public function __construct(
        Context $context,
        private readonly JsonSerializer $jsonSerializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * JSON config consumed by the KO ViewModel on x-magento-init.
     */
    public function getJsonConfig(): string
    {
        return $this->jsonSerializer->serialize([
            'urls' => [
                'getWeek'  => $this->getUrl('magenest_bookingschedule/schedule/getWeek'),
                'save'     => $this->getUrl('magenest_bookingschedule/schedule/save'),
                'copyWeek' => $this->getUrl('magenest_bookingschedule/schedule/copyWeek'),
            ],
            // $this->formKey is injected by the parent backend Template block (FormKey object).
            'formKey'      => $this->formKey->getFormKey(),
            'initialWeek'  => $this->getInitialMonday(),
            'visibleRows'  => 12,
        ]);
    }

    /**
     * Monday (YYYY-MM-DD) of the current ISO week.
     */
    private function getInitialMonday(): string
    {
        $today = new \DateTimeImmutable('today');
        $dayOfWeek = (int) $today->format('N');
        if ($dayOfWeek > 1) {
            $today = $today->modify('-' . ($dayOfWeek - 1) . ' days');
        }
        return $today->format('Y-m-d');
    }
}
