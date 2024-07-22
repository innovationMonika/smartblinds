<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Data\Claim;

use GoMage\Samples\Api\Data\Claim\InfoInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class Info extends AbstractSimpleObject implements InfoInterface
{
    private const FORM_ID        = 'form_id';
    private const COUNTRY_ID     = 'country_id';
    private const POSTCODE       = 'postcode';
    private const HOUSE          = 'house';
    private const APARTMENT      = 'apartment';
    private const STREET         = 'street';
    private const CITY           = 'city';
    private const PREFIX         = 'prefix';
    private const FIRSTNAME      = 'firstname';
    private const MIDDLENAME     = 'middlename';
    private const LASTNAME       = 'lastname';
    private const TELEPHONE      = 'telephone';
    private const EMAIL          = 'email';
    private const CREATE_ACCOUNT = 'create_account';
    private const PASSWORD       = 'password';
    private const ITEMS          = 'items';
    private const GCLID          = 'gclid';
    private const FBP            = 'fbp';
    private const FBC            = 'fbc';

    public function getFormId(): string
    {
        return $this->_get(self::FORM_ID);
    }

    public function setFormId(string $formId)
    {
        return $this->setData(self::FORM_ID, $formId);
    }

    public function getCountryId(): string
    {
        return $this->_get(self::COUNTRY_ID);
    }

    public function setCountryId(string $countryId)
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    public function getPostcode(): string
    {
        return $this->_get(self::POSTCODE);
    }

    public function setPostcode(string $postcode)
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    public function getHouse(): string
    {
        return $this->_get(self::HOUSE);
    }

    public function setHouse(string $house)
    {
        return $this->setData(self::HOUSE, $house);
    }

    public function getApartment(): ?string
    {
        return $this->_get(self::APARTMENT);
    }

    public function setApartment(?string $apartment)
    {
        return $this->setData(self::APARTMENT, $apartment);
    }

    public function getStreet(): string
    {
        return $this->_get(self::STREET);
    }

    public function setStreet(string $street)
    {
        return $this->setData(self::STREET, $street);
    }

    public function getCity(): string
    {
        return $this->_get(self::CITY);
    }

    public function setCity(string $city)
    {
        return $this->setData(self::CITY, $city);
    }

    public function getPrefix(): ?string
    {
        return $this->_get(self::PREFIX);
    }

    public function setPrefix(?string $prefix)
    {
        return $this->setData(self::PREFIX, $prefix);
    }

    public function getFirstname(): string
    {
        return $this->_get(self::FIRSTNAME);
    }

    public function setFirstname(string $firstname)
    {
        return $this->setData(self::FIRSTNAME, $firstname);
    }

    public function getMiddlename(): ?string
    {
        return $this->_get(self::MIDDLENAME);
    }

    public function setMiddlename(?string $middlename)
    {
        return $this->setData(self::MIDDLENAME, $middlename);
    }

    public function getLastname(): string
    {
        return $this->_get(self::LASTNAME);
    }

    public function setLastname(string $lastname)
    {
        return $this->setData(self::LASTNAME, $lastname);
    }

    public function getTelephone(): ?string
    {
        return $this->_get(self::TELEPHONE);
    }

    public function setTelephone(?string $telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    public function getEmail(): string
    {
        return $this->_get(self::EMAIL);
    }

    public function setEmail(string $email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    public function getCreateAccount(): bool
    {
        return $this->_get(self::CREATE_ACCOUNT);
    }

    public function setCreateAccount(bool $createAccount)
    {
        return $this->setData(self::CREATE_ACCOUNT, $createAccount);
    }

    public function getPassword(): ?string
    {
        return $this->_get(self::PASSWORD);
    }

    public function setPassword(?string $password)
    {
        return $this->setData(self::PASSWORD, $password);
    }

    public function getItems()
    {
        return $this->_get(self::ITEMS);
    }

    public function setItems($items)
    {
        return $this->setData(self::ITEMS, $items);
    }

    public function setGclid($value)
    {
        return $this->setData(self::GCLID, $value);
    }

    public function getGclid()
    {
        return $this->_get(self::GCLID);
    }

    public function setFbp($value)
    {
        return $this->setData(self::FBP, $value);
    }

    public function getFbp()
    {
        return $this->_get(self::FBP);
    }

    public function setFbc($value)
    {
        return $this->setData(self::FBC, $value);
    }

    public function getFbc()
    {
        return $this->_get(self::FBC);
    }
}
