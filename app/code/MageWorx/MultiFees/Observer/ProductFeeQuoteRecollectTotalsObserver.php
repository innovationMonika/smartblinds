<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\MultiFees\Model\ProductFeeQuoteRecollectTotalsOnDemand;
use MageWorx\MultiFees\Model\ProductFee;

class ProductFeeQuoteRecollectTotalsObserver implements ObserverInterface
{
    /**
     * @var ProductFeeQuoteRecollectTotalsOnDemand
     */
    protected $recollectTotals;

    /**
     * @param ProductFeeQuoteRecollectTotalsOnDemand $recollectTotals
     */
    public function __construct(ProductFeeQuoteRecollectTotalsOnDemand $recollectTotals)
    {
        $this->recollectTotals = $recollectTotals;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var ProductFee $fee */
        $fee = $observer->getDataObject();

        if (!$fee->isObjectNew() && ((int)$fee->getStatus() === ProductFee::STATUS_DISABLED || $fee->isDeleted())) {
            $this->recollectTotals->execute((int)$fee->getId());
        }
    }
}
