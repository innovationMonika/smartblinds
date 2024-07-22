<?php

namespace GoMage\Samples\Plugin\Quote\Model\ChangeQuoteControl;

use GoMage\Samples\Model\Claim\PlaceOrder\SamplesChecker;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\ChangeQuoteControl;

class AllowSamplesOrders
{
    private SamplesChecker $samplesChecker;

    public function __construct(SamplesChecker $samplesChecker)
    {
        $this->samplesChecker = $samplesChecker;
    }

    public function aroundIsAllowed(
        ChangeQuoteControl $subject,
        callable $proceed,
        CartInterface $quote
    ) {
        $isCreatingSamplesQuote = $this->samplesChecker->isCreatingSamplesQuote();
        $isSamplesQuote = $this->samplesChecker->isSamplesQuote($quote->getId());
        if ($isCreatingSamplesQuote || $isSamplesQuote) {
            return true;
        }
        return $proceed($quote);
    }
}
