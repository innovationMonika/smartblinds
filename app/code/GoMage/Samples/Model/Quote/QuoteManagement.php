<?php

declare(strict_types=1);

namespace GoMage\Samples\Model\Quote;

use GoMage\Samples\Model\Claim\PlaceOrder\SamplesChecker;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\ToOrder as ToOrderConverter;
use Magento\Quote\Model\Quote\Address\ToOrderAddress as ToOrderAddressConverter;
use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Quote\Model\Quote\Item\ToOrderItem as ToOrderItemConverter;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment as ToOrderPaymentConverter;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderFactory;
use Magento\Sales\Api\OrderManagementInterface as OrderManagement;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\SubmitQuoteValidator;
use Magento\Quote\Model\CustomerManagement;

/**
 * Class for managing quote
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class QuoteManagement extends \Magento\Quote\Model\QuoteManagement
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var array
     */
    private $addressesToSync = [];

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    private $remoteAddress;

    private SamplesChecker $samplesChecker;

    /**
     * @param EventManager $eventManager
     * @param SubmitQuoteValidator $submitQuoteValidator
     * @param OrderFactory $orderFactory
     * @param OrderManagement $orderManagement
     * @param CustomerManagement $customerManagement
     * @param ToOrderConverter $quoteAddressToOrder
     * @param ToOrderAddressConverter $quoteAddressToOrderAddress
     * @param ToOrderItemConverter $quoteItemToOrderItem
     * @param ToOrderPaymentConverter $quotePaymentToOrderPayment
     * @param UserContextInterface $userContext
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\CustomerFactory $customerModelFactory
     * @param \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\QuoteIdMaskFactory|null $quoteIdMaskFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface|null $addressRepository
     * @param \Magento\Framework\App\RequestInterface|null $request
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EventManager $eventManager,
        SubmitQuoteValidator $submitQuoteValidator,
        OrderFactory $orderFactory,
        OrderManagement $orderManagement,
        CustomerManagement $customerManagement,
        ToOrderConverter $quoteAddressToOrder,
        ToOrderAddressConverter $quoteAddressToOrderAddress,
        ToOrderItemConverter $quoteItemToOrderItem,
        ToOrderPaymentConverter $quotePaymentToOrderPayment,
        UserContextInterface $userContext,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\CustomerFactory $customerModelFactory,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        SamplesChecker $samplesChecker,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory = null,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository = null,
        \Magento\Framework\App\RequestInterface $request = null,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress = null
    ) {
        $this->samplesChecker = $samplesChecker;
        parent::__construct(
            $eventManager,
            $submitQuoteValidator,
            $orderFactory,
            $orderManagement,
            $customerManagement,
            $quoteAddressToOrder,
            $quoteAddressToOrderAddress,
            $quoteItemToOrderItem,
            $quotePaymentToOrderPayment,
            $userContext,
            $quoteRepository,
            $customerRepository,
            $customerModelFactory,
            $quoteAddressFactory,
            $dataObjectHelper,
            $storeManager,
            $checkoutSession,
            $customerSession,
            $accountManagement,
            $quoteFactory,
            $quoteIdMaskFactory,
            $addressRepository,
            $request,
            $remoteAddress
        );
    }

    /**
     * @inheritdoc
     */
    protected function createCustomerCart($customerId, $storeId)
    {
        $customer = $this->customerRepository->getById($customerId);
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteFactory->create();
        $quote->setStoreId($storeId);
        $quote->setCustomer($customer);
        $quote->setCustomerIsGuest(0);

        return $quote;
    }

    protected function _prepareCustomerQuote($quote)
    {
        if (!$this->samplesChecker->isCreatingSamplesQuote()) {
            parent::_prepareCustomerQuote($quote);
        }

        /** @var Quote $quote */
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        if ($shipping && !$shipping->getSameAsBilling()
            && (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())
        ) {
            if ($shipping->getQuoteId()) {
                $shippingAddress = $shipping->exportCustomerAddress();
            }
            if (isset($shippingAddress)) {
                //save here new customer address
                $shippingAddress->setCustomerId($quote->getCustomerId());
                $this->addressRepository->save($shippingAddress);
                $quote->addCustomerAddress($shippingAddress);
                $shipping->setCustomerAddressData($shippingAddress);
                $this->addressesToSync[] = $shippingAddress->getId();
                $shipping->setCustomerAddressId($shippingAddress->getId());
            }
        }

        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            if ($billing->getQuoteId()) {
                $billingAddress = $billing->exportCustomerAddress();
            }
            if (isset($billingAddress)) {
                $billingAddress->setCustomerId($quote->getCustomerId());
                $this->addressRepository->save($billingAddress);
                $quote->addCustomerAddress($billingAddress);
                $billing->setCustomerAddressData($billingAddress);
                $this->addressesToSync[] = $billingAddress->getId();
                $billing->setCustomerAddressId($billingAddress->getId());
            }
        }
    }
}
