<?php
declare(strict_types=1);

namespace Magenest\FullPageCache\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Session;

class SetGenderContext implements ObserverInterface
{
    public function __construct(
        private HttpContext $httpContext,
        private Session $session
    ) {}

    public function execute(Observer $observer): void
    {
        $val = 'guest';
        if ($this->session->isLoggedIn()) {
            $g = (int)$this->session->getCustomerDataObject()->getGender();
            $val = $g === 1 ? 'male' : ($g === 2 ? 'female' : 'unspecified');
        }
        $this->httpContext->setValue('customer_gender', $val, 'guest');
    }
}
