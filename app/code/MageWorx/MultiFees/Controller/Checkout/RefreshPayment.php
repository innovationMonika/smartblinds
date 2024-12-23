<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Webapi\Exception;

class RefreshPayment extends Action
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \MageWorx\MultiFees\Helper\Data
     */
    protected $helper;

    /**
     * @var \MageWorx\MultiFees\Helper\BillingAddressManager
     */
    protected $billingAddressManager;

    /**
     * @var \MageWorx\MultiFees\Helper\ShippingAddressManager
     */
    protected $shippingAddressManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \MageWorx\MultiFees\Block\LayoutProcessor\PaymentLayoutProcessor
     */
    protected $layoutProcessor;

    /**
     * RefreshPayment constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \MageWorx\MultiFees\Helper\Data $helper
     * @param \MageWorx\MultiFees\Helper\BillingAddressManager $billingAddressManager
     * @param \MageWorx\MultiFees\Helper\ShippingAddressManager $shippingAddressManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Psr\Log\LoggerInterface $logger
     * @param \MageWorx\MultiFees\Block\LayoutProcessor\PaymentLayoutProcessor $layoutProcessor
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \MageWorx\MultiFees\Helper\Data $helper,
        \MageWorx\MultiFees\Helper\BillingAddressManager $billingAddressManager,
        \MageWorx\MultiFees\Helper\ShippingAddressManager $shippingAddressManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart,
        \Psr\Log\LoggerInterface $logger,
        \MageWorx\MultiFees\Block\LayoutProcessor\PaymentLayoutProcessor $layoutProcessor
    ) {
        parent::__construct($context);
        $this->helper                 = $helper;
        $this->billingAddressManager  = $billingAddressManager;
        $this->shippingAddressManager = $shippingAddressManager;
        $this->quoteRepository        = $quoteRepository;
        $this->storeManager           = $storeManager;
        $this->cart                   = $cart;
        $this->logger                 = $logger;
        $this->layoutProcessor        = $layoutProcessor;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws Exception
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute()
    {
        /** @var Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if (!$this->helper->isEnable()) {
            $result->setData([]);

            return $result;
        }

       if ($this->_request->getParam('payment_method')) {
            $method  = $this->_request->getParam('payment_method');
            $payment = $this->cart->getCheckoutSession()->getQuote()->getPayment();
            if (!$payment->getMethod() ||
                $payment->getMethod() != $method) {
                $payment->setMethod($method);
                $payment->getResource()->save($payment);
            }

            $billingAddress = $this->billingAddressManager->transferBillingAddressDataToTheAddressObject(
                $this->getRequest()->getParam('billingAddressData')
            );

            if ($this->_request->getParam('shipping_method')) {
                $billingAddress->setShippingMethod($this->_request->getParam('shipping_method'));
            }

            $baseLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['beforeMethods']['children']
            ['mageworx-payment-fee-form-container']['children']['mageworx-payment-fee-form-fieldset']['children'] = [];

            $layout = $this->layoutProcessor->process($baseLayout);

            $result->setData(
                $layout['components']['checkout']['children']['steps']['children']['billing-step']
                ['children']['payment']['children']['beforeMethods']['children']
                ['mageworx-payment-fee-form-container']['children']['mageworx-payment-fee-form-fieldset']['children']
            );

            return $result;
        } else {
            throw new Exception(__('Unknown fee type for update. Available type: payment_method'));
        }
    }
}
