<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ananta\ImprovedCheckout\Plugin\Checkout;

use Magento\Checkout\Model\Session;

class Data
{
    /**
     * @var $session
     */
    protected $session;

    /**
     * construct function of class
     *
     * @param Session $session
     */
    public function __construct(
        Session $session
    ) {
        $this->session = $session;
    }

    /**
     * If quote is virtual display billing address on payment page
     *
     * @param \Magento\Checkout\Helper\Data $subject
     * @param \Closure $proceed
     */
    public function aroundIsDisplayBillingOnPaymentMethodAvailable(
        \Magento\Checkout\Helper\Data $subject,
        \Closure $proceed
    ) {
        $quote = $this->session->getQuote();
        if ($quote->getIsVirtual()) {
            return (bool)true;
        }
        $result = $proceed();
        return $result;
    }
}
