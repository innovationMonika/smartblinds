<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\MultiFees\Model\FeeQuoteRecollectTotalsOnDemand;
use MageWorx\MultiFees\Model\AbstractFee;

class FeeQuoteRecollectTotalsObserver implements ObserverInterface
{
    /**
     * @var FeeQuoteRecollectTotalsOnDemand
     */
    protected $recollectTotals;

    /**
     * @param FeeQuoteRecollectTotalsOnDemand $recollectTotals
     */
    public function __construct(FeeQuoteRecollectTotalsOnDemand $recollectTotals)
    {
        $this->recollectTotals = $recollectTotals;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var AbstractFee $fee */
        $fee = $observer->getDataObject();

        if (!$fee->isObjectNew() && ((int)$fee->getStatus() === AbstractFee::STATUS_DISABLED || $fee->isDeleted())) {
            $this->recollectTotals->execute((int)$fee->getId());
        }
    }
}
