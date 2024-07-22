<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Checkout;

use MageWorx\MultiFees\Api\QuoteProductFeeManagerInterface;

class ProductFee extends AbstractFee
{
    /**
     * @var QuoteProductFeeManagerInterface
     */
    protected $quoteFeeManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param QuoteProductFeeManagerInterface $quoteFeeManager
     * @param \MageWorx\MultiFees\Helper\BillingAddressManager $billingAddressManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        QuoteProductFeeManagerInterface $quoteFeeManager,
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
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $quoteItemId = $this->getRequest()->getPost('quote_item_id');

        if ($quoteItemId) {
            $this->quoteFeeManager->setCurrentItemId((int)$quoteItemId);
        }

        return parent::execute();
    }

    /**
     * @param array $feesPost
     * @return mixed
     */
    protected function prepareFeeData($feesPost)
    {
        foreach ($feesPost as $feeId => $itemData) {
            foreach ($itemData as $itemId => $feeData) {
                $requestedItemId = $this->getRequest()->getPost('quote_item_id');
                if ($requestedItemId && $itemId != $requestedItemId) {
                    unset($feesPost[$feeId][$itemId]);
                    continue;
                }

                if (empty($feeData['options'])) {
                    continue;
                }

                $options    = is_array($feeData['options']) ? $feeData['options'] : explode(',', $feeData['options']);
                $newOptions = [];
                foreach ($options as $option) {
                    $newOptions[$option] = [];
                }

                $feesPost[$feeId][$itemId]['options'] = $newOptions;
                $feesPost[$feeId][$itemId]['type']    = $this->getRequest()->getPost('type');
            }
        }

        return $feesPost;
    }
}
