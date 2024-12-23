<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\MultiFees\Api\QuoteFeeManagerInterface;

class AddFeeToOrderAdminObserver implements ObserverInterface
{
    /**
     * @var \MageWorx\MultiFees\Helper\Fee
     */
    protected $feeHelper;

    /**
     * @var QuoteFeeManagerInterface
     */
    protected $quoteFeeManager;

    /**
     * AddFeeToOrder constructor.
     *
     * @param \MageWorx\MultiFees\Helper\Data $feeHelper
     */
    public function __construct(
        QuoteFeeManagerInterface $quoteFeeManager,
        \MageWorx\MultiFees\Helper\Data $feeHelper
    ) {
        $this->quoteFeeManager = $quoteFeeManager;
        $this->feeHelper       = $feeHelper;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // check submit fees when admin/sales_order_create
        $post     = $observer->getEvent()->getRequest();
        $feesPost = isset($post['fee']) ? $post['fee'] : [];

        if (!empty($feesPost)) {
            foreach ($feesPost as $feeId => $feeData) {
                if (empty($feeData['options'])) {
                    continue;
                }

                $normalOptionsData = [];
                foreach ($feeData['options'] as $key => $optionId) {
                    $normalOptionsData[$optionId] = $optionId;
                }

                $feesPost[$feeId]['options'] = $normalOptionsData;
            }
        }

        if (isset($post['fee_type'])) {
            $quote = $this->feeHelper->getQuote();
            $this->quoteFeeManager->addFeesToQuote(
                $feesPost,
                $quote,
                true,
                0,
                $this->quoteFeeManager->getAddressFromQuote($quote)->getId()
            );
        }
    }
}
