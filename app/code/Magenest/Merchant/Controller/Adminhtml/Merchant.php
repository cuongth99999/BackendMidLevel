<?php
/**
 * app/code/Magenest/Merchant/Controller/Adminhtml/Merchant.php
 *
 * Abstract base used to centralise the ACL check for all merchant actions.
 */
declare(strict_types=1);

namespace Magenest\Merchant\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Merchant extends Action
{
    public const ADMIN_RESOURCE = 'Magenest_Merchant::merchant_manage';

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
