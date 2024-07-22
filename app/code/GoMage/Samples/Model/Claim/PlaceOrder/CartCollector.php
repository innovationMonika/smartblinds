<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Claim\PlaceOrder;

use GoMage\Samples\Api\Data\Claim\InfoInterface;
use GoMage\Samples\Exception\Claim\PlaceOrderException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\Method\Free;
use GoMage\Samples\Model\Quote\QuoteManagement;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;

class CartCollector
{
    /**
     * @var QuoteManagement
     */
    private QuoteManagement $cartManagement;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var CustomerCreator
     */
    private CustomerCreator $customerCreator;

    /**
     * @var FreeShippingBuilder
     */
    private FreeShippingBuilder $freeShippingBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @var SamplesChecker
     */
    private SamplesChecker $samplesChecker;

    /**
     * @var AddressRepositoryInterface
     */
    private AddressRepositoryInterface $addressRepository;

    /**
     * @param QuoteManagement $cartManagement
     * @param CartRepositoryInterface $cartRepository
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerCreator $customerCreator
     * @param FreeShippingBuilder $freeShippingBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param SamplesChecker $samplesChecker
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        QuoteManagement $cartManagement,
        CartRepositoryInterface $cartRepository,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        CustomerCreator $customerCreator,
        FreeShippingBuilder $freeShippingBuilder,
        CustomerRepositoryInterface $customerRepository,
        SamplesChecker $samplesChecker,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->cartManagement = $cartManagement;
        $this->cartRepository = $cartRepository;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->customerCreator = $customerCreator;
        $this->freeShippingBuilder = $freeShippingBuilder;
        $this->customerRepository = $customerRepository;
        $this->samplesChecker = $samplesChecker;
        $this->addressRepository = $addressRepository;
    }

    public function collect(InfoInterface $info): int
    {
        $customer = $this->getCustomer($info->getEmail());
        $this->samplesChecker->setIsCreatingSamplesQuote(true);
        if ($customer) {
            $cartId = $this->cartManagement->createEmptyCartForCustomer($customer->getId());
        } else {
            $cartId = $this->cartManagement->createEmptyCart();
        }
        $this->samplesChecker->setIsCreatingSamplesQuote(false);

        $this->samplesChecker->setSamplesQuote($cartId);

        $cart = $this->cartRepository->get($cartId);
        $cart->setStoreId($this->storeManager->getStore()->getId());

        $cart->setCustomerEmail($info->getEmail());

        if (!$customer && $info->getCreateAccount()) {
            $customer = $this->customerCreator->create($info);
        }

        if ($customer) {
            $cart->assignCustomer($customer);
            $cart->setCustomerIsGuest(false);
        }

        $this->setItems($cart, $info);
        $this->setAddresses($cart, $info, $customer);
        $this->setShipping($cart);
        $this->setPayment($cart);

        $cart->setInventoryProcessed(false);

        $this->cartRepository->save($cart);

        return (int) $cartId;
    }

    private function setItems(CartInterface $cart, InfoInterface $info)
    {
        foreach ($info->getItems() as $item) {
            try {
                $product = $this->productRepository->getById($item->getId());
            } catch (NoSuchEntityException $e) {
                throw new PlaceOrderException(
                    __( "We have no more samples for product %1. Please remove it from basket", $item->getName())
                );
            }
            $product->setSkipCheckRequiredOption(true);
            $product->setPrice(0);
            $cart->addProduct($product);
        }
    }

    private function setAddresses(CartInterface $cart, InfoInterface $info, ?CustomerInterface $customer)
    {
        $hasDefaultAddress = false;
        if ($customer && $customer->getDefaultShipping()) {
            try {
                $this->addressRepository->getById($customer->getDefaultShipping());
                $hasDefaultAddress = true;
            } catch (NoSuchEntityException $e) {
                $customer
                    ->setDefaultBilling(null)
                    ->setDefaultShipping(null);
                $this->customerRepository->save($customer);
            }
        }

        $addressData = [
            'firstname'   => $info->getFirstname(),
            'middlename'  => $info->getMiddlename(),
            'lastname'    => $info->getLastname(),
            'telephone'   => $info->getTelephone(),
            'email'       => $info->getEmail(),
            'country_id'  => $info->getCountryId(),
            'city'        => $info->getCity(),
            'postcode'    => $info->getPostcode(),
            'street'      => implode(' ', [$info->getStreet(), $info->getHouse(), $info->getApartment()]),
            'prefix'      => $info->getPrefix(),
            'samples_form_id'      => $info->getFormId(),
            'customer_address_id'  => null,
            'save_in_address_book' => !$hasDefaultAddress,
            'should_ignore_validation' => true
        ];
        $cart->getBillingAddress()->addData($addressData);
        $cart->getShippingAddress()->addData($addressData);
    }

    private function setShipping(CartInterface $cart)
    {
        $shippingAddress = $cart->getShippingAddress();
        $shippingAddress->setCollectShippingRates(false);
        $shippingAddress->setShippingMethod('freeshipping_freeshipping');
        if (!$shippingAddress->getShippingRateByCode('freeshipping_freeshipping')) {
            $shippingAddress->addShippingRate($this->freeShippingBuilder->build($cart->getStoreId()));
        }
    }

    private function setPayment(CartInterface $cart)
    {
        $cart->getPayment()->importData(['method' => Free::PAYMENT_METHOD_FREE_CODE]);
    }

    private function getCustomer(string $email): ?CustomerInterface
    {
        try {
            return $this->customerRepository->get($email);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
