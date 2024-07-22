<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Cart;

use Magento\Framework\Exception\LocalizedException;
use MageWorx\MultiFees\Exception\RefactoringException;
use MageWorx\MultiFees\Api\QuoteFeeManagerInterface;

class Fee extends \Magento\Checkout\Controller\Cart
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var QuoteFeeManagerInterface
     */
    protected $quoteFeeManager;

    /**
     * Fee constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param QuoteFeeManagerInterface $quoteFeeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        QuoteFeeManagerInterface $quoteFeeManager
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->quoteFeeManager       = $quoteFeeManager;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Initialize fees
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws LocalizedException
     * @throws RefactoringException
     */
    public function execute()
    {
        $this->getRequest()->getParams();
        $feesPost  = $this->getRequest()->getPost('fee');
        $cartQuote = $this->cart->getQuote();
        $type      = $this->getRequest()->getPost('type');

        $this->quoteFeeManager->addFeesToQuote(
            $feesPost,
            $cartQuote,
            true,
            $type,
            $this->quoteFeeManager->getAddressFromQuote($cartQuote)->getId()
        );

        try {
            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $this->quoteRepository->save($cartQuote);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We cannot apply the fees.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        return $this->_goBack();
    }
}
