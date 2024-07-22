<?php declare(strict_types=1);

namespace GoMage\Samples\Plugin\Customer\CustomerData\Customer;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\CustomerData\Customer;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\DataObject;

class AddSamplesFormData
{
    private CurrentCustomer $currentCustomer;

    public function __construct(CurrentCustomer $currentCustomer)
    {
        $this->currentCustomer = $currentCustomer;
    }

    public function afterGetSectionData(Customer $subject, array $result)
    {
        if (!$this->currentCustomer->getCustomerId()) {
            return $result;
        }
        $customer = $this->currentCustomer->getCustomer();
        $defaultShipping = $customer->getDefaultShipping();
        $filteredAddresses = array_values(
            array_filter($customer->getAddresses(), function (AddressInterface $address) use ($defaultShipping) {
                return $address->getId() == $defaultShipping;
            })
        );
        $address = reset($filteredAddresses);
        if (!$address) {
            $address = new DataObject();
        }
        $data = [
            'country'    => $address->getCountryId(),
            'postcode'   => $address->getPostcode(),
            'house'      => $address->getStreet()[1] ?? null,
            'apartment'  => $address->getStreet()[2] ?? null,
            'street'     => $address->getStreet()[0] ?? null,
            'city'       => $address->getCity(),
            'gender'     => $customer->getGender(),
            'prefix'     => $customer->getPrefix(),
            'firstname'  => $customer->getFirstname(),
            'middlename' => $customer->getMiddlename(),
            'lastname'   => $customer->getLastname(),
            'telephone'  => $address->getTelephone(),
            'email'      => $customer->getEmail()
        ];
        $result['samplesFormData'] = $data;
        return $result;
    }
}
