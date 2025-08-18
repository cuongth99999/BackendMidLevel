<?php
declare(strict_types=1);

namespace Magenest\FullPageCache\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;

class Gender implements SectionSourceInterface
{

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Session $session
     */
    public function __construct(
        Session $session
    ) {
        $this->session = $session;
    }

    public function getSectionData(): array
    {
        $v = 'guest';
        if ($this->session->isLoggedIn()) {
            $g = (int)$this->session->getCustomerDataObject()->getGender();
            $v = $g === 1 ? 'male' : ($g === 2 ? 'female' : 'unspecified');
        }
        return ['gender' => $v];
    }
}
