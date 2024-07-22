<?php declare(strict_types=1);

namespace GoMage\Samples\Plugin\Customer\CustomerData\Customer;

use Magento\Customer\CustomerData\Customer;
use Magento\Customer\Helper\Session\CurrentCustomer;

class AddEmail
{
    private CurrentCustomer $currentCustomer;

    public function __construct(CurrentCustomer $currentCustomer)
    {
        $this->currentCustomer = $currentCustomer;
    }

    public function afterGetSectionData(Customer $subject, array $result)
    {
        if ($this->currentCustomer->getCustomerId()) {
            $result['email'] = $this->currentCustomer->getCustomer()->getEmail();
        }
        return $result;
    }
}
