<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use MageWorx\MultiFees\Api\CartFeeRepositoryInterface;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Api\PaymentFeeRepositoryInterface;
use MageWorx\MultiFees\Api\ProductFeeRepositoryInterface;
use MageWorx\MultiFees\Api\ShippingFeeRepositoryInterface;
use MageWorx\MultiFees\Exception\RefactoringException;
use MageWorx\MultiFees\Model\AbstractFee;
use MageWorx\MultiFees\Model\AbstractFee as FeeModel;

/**
 * Config Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Error code for the required cart fee missed in the quote
     */
    const ERROR_REQUIRED_CART_FEE_MISS = 261503;

    /**
     * Error code for the required shipping fee missed in the quote
     */
    const ERROR_REQUIRED_SHIPPING_FEE_MISS = 261504;

    /**
     * Error code for the required payment fee missed in the quote
     */
    const ERROR_REQUIRED_PAYMENT_FEE_MISS = 261505;

    /**#@+
     * Config paths to settings
     */
    const FEE_CART_ENABLE                  = 'mageworx_multifees/main/enable_cart';
    const FEE_DISPLAY_PRODUCT_FEE          = 'mageworx_multifees/main/display_product_fee';
    const FEE_PRODUCT_FEE_BLOCK_LABEL      = 'mageworx_multifees/main/product_fee_block_label';
    const FEE_PRODUCT_FEE_POSITION         = 'mageworx_multifees/main/product_fee_position';
    const FEE_APPLY_ON_CLICK               = 'mageworx_multifees/main/apply_fee_on_click';
    const FEE_INCLUDE_IN_SHIPPING_PRICE    = 'mageworx_multifees/main/include_fee_in_shipping_price';
    const FEE_TAX_CALCULATION_INCLUDES_TAX = 'mageworx_multifees/main/tax_calculation_includes_tax';
    const FEE_DISPLAY_TAX_IN_BLOCK         = 'mageworx_multifees/main/display_tax_in_block';
    const FEE_DISPLAY_TAX_IN_CART          = 'mageworx_multifees/main/display_tax_in_cart';
    const FEE_DISPLAY_TAX_IN_SALES         = 'mageworx_multifees/main/display_tax_in_sales';
    const FEE_DETAILS_EXPAND_IN_PDF        = 'mageworx_multifees/main/expand_fee_details_in_pdf';

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $adminQuoteSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $quoteItemFactory;

    /**
     * @var CartFeeRepositoryInterface
     */
    protected $cartFeeRepository;

    /**
     * @var ShippingFeeRepositoryInterface
     */
    protected $shippingFeeRepository;

    /**
     * @var PaymentFeeRepositoryInterface
     */
    protected $paymentFeeRepository;

    /**
     * @var ProductFeeRepositoryInterface
     */
    protected $productFeeRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var int
     */
    protected $currentQuoteItemId;

    /**
     * Data constructor.
     *
     * @param SerializerInterface $serializer
     * @param Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session\Quote $adminQuoteSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        CartFeeRepositoryInterface $cartFeeRepository,
        ShippingFeeRepositoryInterface $shippingFeeRepository,
        PaymentFeeRepositoryInterface $paymentFeeRepository,
        ProductFeeRepositoryInterface $productFeeRepository,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Framework\Registry $coreRegistry,
        SerializerInterface $serializer,
        Context $context
    ) {
        $this->adminQuoteSession     = $adminQuoteSession;
        $this->checkoutSession       = $checkoutSession;
        $this->appState              = $appState;
        $this->objectManager         = $objectManager;
        $this->quoteItemFactory      = $quoteItemFactory;
        $this->coreRegistry          = $coreRegistry;
        $this->serializer            = $serializer;
        $this->cartFeeRepository     = $cartFeeRepository;
        $this->shippingFeeRepository = $shippingFeeRepository;
        $this->paymentFeeRepository  = $paymentFeeRepository;
        $this->productFeeRepository  = $productFeeRepository;
        parent::__construct($context);
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isEnable($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::FEE_CART_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isDisplayProductFee($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::FEE_DISPLAY_PRODUCT_FEE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getProductFeeBlockLabel($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::FEE_PRODUCT_FEE_BLOCK_LABEL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getProductFeePosition($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::FEE_PRODUCT_FEE_POSITION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is a fee should be applied when the corresponding fee clicked (Yes)
     * or by pressing the button "Apply Fee" (No)
     *
     * @param null|int $storeId
     * @return bool
     */
    public function isApplyOnClick($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::FEE_APPLY_ON_CLICK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isIncludeFeeInShippingPrice($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::FEE_INCLUDE_IN_SHIPPING_PRICE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isTaxCalculationIncludesTax($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::FEE_TAX_CALCULATION_INCLUDES_TAX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getTaxInCart($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::FEE_DISPLAY_TAX_IN_CART,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getTaxInSales($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::FEE_DISPLAY_TAX_IN_SALES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getTaxInBlock($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::FEE_DISPLAY_TAX_IN_BLOCK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is a fee details should be expanded in the printed .pdf ?
     *
     * No (false) - all fees grouped in one row in totals, having overall amount.
     * Yes (true) - each fee displayed in separate row, having own amount.
     *
     * @important Depends on (compatible with) self::getTaxInSales() setting.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function expandFeeDetailsInPdf($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::FEE_DETAILS_EXPAND_IN_PDF,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $value
     * @return array|bool|float|int|string|null
     */
    public function unserializeValue($value)
    {
        return $value ? $this->serializer->unserialize($value) : [];
    }

    /**
     * @param array $value
     * @return bool|string
     */
    public function serializeValue($value)
    {
        return $this->serializer->serialize($value);
    }

    /**
     * @return bool
     */
    public function isProductPage()
    {
        return $this->_getRequest()->getFullActionName() == 'catalog_product_view';
    }

    /**
     * Get current checkout quote
     *
     * @return \Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuote()
    {
        if ($this->appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return $this->adminQuoteSession->getQuote();
        }

        return $this->checkoutSession->getQuote();
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param array $totalsArray
     * @param AbstractFee $fee
     * @param array $validItems
     * @return float|int
     */
    public function getBaseFeeLeft($total, array $totalsArray, $fee, $validItems)
    {
        $baseMageWorxFeeLeft = 0;

        if ($fee->getCountPercentFrom() == FeeModel::FEE_COUNT_PERCENT_FROM_PRODUCT) {
            foreach ($validItems as $item) {
                $baseMageWorxFeeLeft += $item->getPrice() * $item->getQty();
            }

            return $baseMageWorxFeeLeft;
        }

        $baseSubtotal = floatval($total->getBaseSubtotalWithDiscount());
        $baseShipping = floatval($total->getBaseShippingAmount()); // - $address->getBaseShippingTaxAmount()
        $baseTax      = floatval($total->getBaseTaxAmount());

        foreach ($totalsArray as $field) {
            switch ($field) {
                case 'subtotal':
                    $baseMageWorxFeeLeft += $baseSubtotal;
                    break;
                case 'shipping':
                    $baseMageWorxFeeLeft += $baseShipping;
                    break;
                case 'tax':
                    $baseMageWorxFeeLeft += $baseTax;
                    break;
            }
        }

        return $baseMageWorxFeeLeft;
    }

    /**
     *
     * @param \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote $quote
     *
     * @param string $type
     * @return \Magento\Quote\Api\Data\AddressInterface|\Magento\Quote\Model\Quote\Address
     */
    public function getSalesAddress(
        \Magento\Quote\Api\Data\CartInterface $quote,
        $type = \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING
    ) {
        /** @var \Magento\Quote\Model\Quote\Address $address */
        switch ($type) {
            case \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_BILLING:
                $address = $quote->getBillingAddress();
                break;
            case \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING:
            default:
                $address = $quote->getShippingAddress();
                if (!$address->getSubtotal()) {
                    $address = $quote->getBillingAddress();
                }
        }

        return $address;
    }

    /**
     * @return \Magento\Backend\Model\Session\Quote|\Magento\Checkout\Model\Session
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentSession()
    {
        $areaCode = $this->appState->getAreaCode();
        if ($areaCode == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return $this->objectManager->get('Magento\Backend\Model\Session\Quote');
        }

        return $this->checkoutSession;
    }

    /**
     * Find and return repository by fee type
     *
     * @param int $type
     * @return CartFeeRepositoryInterface|PaymentFeeRepositoryInterface|ShippingFeeRepositoryInterface
     * @throws RefactoringException
     */
    public function getSuitableFeeRepositoryByType($type)
    {
        switch ($type) {
            case FeeInterface::CART_TYPE:
                $repository = $this->cartFeeRepository;
                break;
            case FeeInterface::SHIPPING_TYPE:
                $repository = $this->shippingFeeRepository;
                break;
            case FeeInterface::PAYMENT_TYPE:
                $repository = $this->paymentFeeRepository;
                break;
            case FeeInterface::PRODUCT_TYPE:
                $repository = $this->productFeeRepository;
                break;
            default:
                throw new RefactoringException(__('Unspecified fee type to detect suitable repository #261840'));
        }

        return $repository;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveAddressDataToSession($address)
    {
        $session = $this->getCurrentSession();
        if ($address->getPostcode()) {
            $session->setMageworxAddressPostcode($address->getPostcode());
        }
        if ($address->getCountryId()) {
            $session->setMageworxAddressCountryId($address->getCountryId());
        }
        if ($address->getRegionId()) {
            $session->setMageworxAddressRegionId($address->getRegionId());
        }

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddressDataFromSession($address)
    {
        $session = $this->getCurrentSession();

        if (!$address->getPostcode()) {
            $address->setPostode($session->getMageworxAddressPostcode());
        }

        if (!$address->getCountryId()) {
            $address->setCountryId($session->getMageworxAddressCountryId());
        }

        if (!$address->getRegionId()) {
            $address->setRegionId($session->getMageworxAddressRegionId());
        }

        return $address;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuoteItemFromCurrentProduct()
    {
        $product = $this->getCurrentProduct();
        // create quote item from current product on product page to calculate hidden fee
        $quoteItem = $this->quoteItemFactory->create();
        $quoteItem->setProduct($product);
        $quoteItem->setQty(1);

        return $quoteItem;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item|null
     */
    public function getCurrentItem()
    {
        return $this->coreRegistry->registry('current_item');
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return $this
     */
    public function setCurrentItem(\Magento\Quote\Model\Quote\Item $item)
    {
        if ($this->coreRegistry->registry('current_item')) {
            $this->coreRegistry->unregister('current_item');
        }
        $this->coreRegistry->register('current_item', $item);

        return $this;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentQuoteItemId()
    {
        if ($this->currentQuoteItemId) {
            return $this->currentQuoteItemId;
        }
        $quoteItemId = $this->_getRequest()->getPost('quote_item_id');

        if (!$quoteItemId && $this->getCurrentItem()) {
            $quoteItemId = $this->getCurrentItem()->getItemId();
        }

        return $quoteItemId;
    }


    /**
     * Return current product from registry on product page
     * OR return product from quote item on cart page
     *
     * @return \Magento\Catalog\Model\Product|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentProduct()
    {
        $product = $this->coreRegistry->registry('current_product');
        if ($product) {
            return $product;
        }

        $item = $this->getCurrentItem();
        if ($item) {
            $product = $item->getProduct();
            $this->coreRegistry->register('current_product', $product);

            return $product;
        }

        return null;
    }
}
