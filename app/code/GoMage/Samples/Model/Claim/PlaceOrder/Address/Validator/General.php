<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Claim\PlaceOrder\Address\Validator;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Directory\Helper\Data;
use Magento\Eav\Model\Config;
use Zend_Validate;

class General
{
    private $eavConfig;
    private $directoryData;

    public function __construct(
        Config $eavConfig,
        Data $directoryData
    ) {
        $this->eavConfig = $eavConfig;
        $this->directoryData = $directoryData;
    }

    public function validate(AbstractAddress $address)
    {
        $errors = array_merge(
            $this->checkRequiredFields($address),
            $this->checkOptionalFields($address)
        );

        return $errors;
    }

    private function checkRequiredFields(AbstractAddress $address)
    {
        $errors = [];
        if (!Zend_Validate::is($address->getFirstname(), 'NotEmpty')) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'firstname']);
        }

        if (!Zend_Validate::is($address->getLastname(), 'NotEmpty')) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'lastname']);
        }

        if (!Zend_Validate::is($address->getStreetLine(1), 'NotEmpty')) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'street']);
        }

        if (!Zend_Validate::is($address->getCity(), 'NotEmpty')) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'city']);
        }

        return $errors;
    }

    private function checkOptionalFields(AbstractAddress $address)
    {
        $this->reloadAddressAttributes($address);

        $errors = [];

        if ($this->isFaxRequired()
            && !Zend_Validate::is($address->getFax(), 'NotEmpty')
        ) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'fax']);
        }

        if ($this->isCompanyRequired()
            && !Zend_Validate::is($address->getCompany(), 'NotEmpty')
        ) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'company']);
        }

        $havingOptionalZip = $this->directoryData->getCountriesWithOptionalZip();
        if (!in_array($address->getCountryId(), $havingOptionalZip)
            && !Zend_Validate::is($address->getPostcode(), 'NotEmpty')
        ) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'postcode']);
        }

        return $errors;
    }

    private function isCompanyRequired()
    {
        return $this->eavConfig->getAttribute('customer_address', 'company')->getIsRequired();
    }

    private function isFaxRequired()
    {
        return $this->eavConfig->getAttribute('customer_address', 'fax')->getIsRequired();
    }

    private function reloadAddressAttributes(AbstractAddress $address): void
    {
        $attributeSetId = $address->getAttributeSetId() ?: AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS;
        $address->setData('attribute_set_id', $attributeSetId);
        $this->eavConfig->getEntityAttributes(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $address);
    }
}
