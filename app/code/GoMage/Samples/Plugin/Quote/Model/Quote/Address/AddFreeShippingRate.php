<?php declare(strict_types=1);

namespace GoMage\Samples\Plugin\Quote\Model\Quote\Address;

use GoMage\Samples\Model\Claim\PlaceOrder\FreeShippingBuilder;
use GoMage\Samples\Model\Claim\PlaceOrder\SamplesChecker;
use Magento\Quote\Model\Quote\Address;

class AddFreeShippingRate
{
    private SamplesChecker $samplesChecker;
    private FreeShippingBuilder $freeShippingBuilder;

    public function __construct(
        SamplesChecker $samplesChecker,
        FreeShippingBuilder $freeShippingBuilder
    ) {
        $this->samplesChecker = $samplesChecker;
        $this->freeShippingBuilder = $freeShippingBuilder;
    }

    public function afterRequestShippingRates(
        Address $subject,
        $result
    ) {
        if (!$this->samplesChecker->isSamplesQuote($subject->getQuote()->getId())) {
            return $result;
        }

        if (!$subject->getShippingRateByCode('freeshipping_freeshipping')) {
            $subject->addShippingRate($this->freeShippingBuilder->build($subject->getQuote()->getStoreId()));
        }

        return true;
    }
}
