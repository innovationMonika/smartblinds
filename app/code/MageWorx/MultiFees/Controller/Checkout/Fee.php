<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Checkout;

use MageWorx\MultiFees\Api\QuoteFeeManagerInterface;

class Fee extends AbstractFee
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
     * @var \MageWorx\MultiFees\Helper\BillingAddressManager
     */
    protected $billingAddressManager;

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
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param QuoteFeeManagerInterface $quoteFeeManager
     * @param \MageWorx\MultiFees\Helper\BillingAddressManager $billingAddressManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        QuoteFeeManagerInterface $quoteFeeManager,
        \MageWorx\MultiFees\Helper\BillingAddressManager $billingAddressManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        parent::__construct(
            $context,
            $quoteRepository,
            $billingAddressManager,
            $storeManager,
            $cart,
            $logger,
            $serializer
        );
        $this->quoteFeeManager = $quoteFeeManager;
    }

    /**
     * @param array $feesPost
     * @return mixed
     */
    protected function prepareFeeData($feesPost)
    {
        foreach ($feesPost as $feeId => $feeData) {
            if (empty($feeData['options'])) {
                continue;
            }

            $options    = is_array($feeData['options']) ? $feeData['options'] : explode(',', $feeData['options']);
            $newOptions = [];
            foreach ($options as $option) {
                $newOptions[$option] = [];
            }

            $feesPost[$feeId]['options'] = $newOptions;
            $feesPost[$feeId]['type']    = $this->getRequest()->getPost('type');
        }

        return $feesPost;
    }
}
