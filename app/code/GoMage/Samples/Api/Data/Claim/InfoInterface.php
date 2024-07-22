<?php declare(strict_types=1);

namespace GoMage\Samples\Api\Data\Claim;

/**
 * @api
 */
interface InfoInterface
{
    /**
     * @return string
     */
    public function getFormId(): string;

    /**
     * @param string $formId
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setFormId(string $formId);

    /**
     * @return string
     */
    public function getCountryId(): string;

    /**
     * @param string $countryId
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setCountryId(string $countryId);

    /**
     * @return string
     */
    public function getPostcode(): string;

    /**
     * @param string $postcode
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setPostcode(string $postcode);

    /**
     * @return string
     */
    public function getHouse(): string;

    /**
     * @param string $house
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setHouse(string $house);

    /**
     * @return string|null
     */
    public function getApartment(): ?string;

    /**
     * @param string|null $apartment
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setApartment(?string $apartment);

    /**
     * @return string
     */
    public function getStreet(): string;

    /**
     * @param string $street
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setStreet(string $street);

    /**
     * @return string
     */
    public function getCity(): string;

    /**
     * @param string $city
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setCity(string $city);

    /**
     * @return string|null
     */
    public function getPrefix(): ?string;

    /**
     * @param string|null $prefix
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setPrefix(?string $prefix);

    /**
     * @return string
     */
    public function getFirstname(): string;

    /**
     * @param string $firstname
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setFirstname(string $firstname);

    /**
     * @return string|null
     */
    public function getMiddlename(): ?string;

    /**
     * @param string|null $middlename
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setMiddlename(?string $middlename);

    /**
     * @return string
     */
    public function getLastname(): string;

    /**
     * @param string $lastname
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setLastname(string $lastname);

    /**
     * @return string|null
     */
    public function getTelephone(): ?string;

    /**
     * @param string|null $telephone
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setTelephone(?string $telephone);

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @param string $email
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setEmail(string $email);

    /**
     * @return bool
     */
    public function getCreateAccount(): bool;

    /**
     * @param bool $createAccount
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setCreateAccount(bool $createAccount);

    /**
     * @return string|null
     */
    public function getPassword(): ?string;

    /**
     * @param string|null $password
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setPassword(?string $password);

    /**
     * @return \GoMage\Samples\Api\Data\Claim\Info\ItemInterface[]
     */
    public function getItems();

    /**
     * @param \GoMage\Samples\Api\Data\Claim\Info\ItemInterface[] $items
     * @return \GoMage\Samples\Api\Data\Claim\InfoInterface
     */
    public function setItems($items);

    /**
     * @param $value
     * @return mixed
     */
    public function setGclid($value);

    /**
     * @return string|null
     */
    public function getGclid();

    /**
     * @param $value
     * @return mixed
     */
    public function setFbp($value);

    /**
     * @return string|null
     */
    public function getFbp();

    /**
     * @param $value
     * @return mixed
     */
    public function setFbc($value);

    /**
     * @return string|null
     */
    public function getFbc();
}
