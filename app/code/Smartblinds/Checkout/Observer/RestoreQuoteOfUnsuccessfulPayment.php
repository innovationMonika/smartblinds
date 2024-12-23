<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 *  See COPYING.txt for license details.
 */

namespace Smartblinds\Checkout\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class RestoreQuoteOfUnsuccessfulPayment implements ObserverInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;



    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $this->checkoutSession->getLastRealOrder();
        $payment = $order->getPayment();

        if (!$payment) {
            return;
        }
        $this->checkoutSession->restoreQuote();
    }
}
