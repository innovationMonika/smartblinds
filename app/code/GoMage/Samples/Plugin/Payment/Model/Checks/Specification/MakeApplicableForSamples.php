<?php declare(strict_types=1);

namespace GoMage\Samples\Plugin\Payment\Model\Checks\Specification;

use GoMage\Samples\Model\Claim\PlaceOrder\SamplesChecker;
use Magento\Payment\Model\Checks\SpecificationInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

class MakeApplicableForSamples
{
    private SamplesChecker $samplesChecker;

    public function __construct(SamplesChecker $samplesChecker)
    {
        $this->samplesChecker = $samplesChecker;
    }

    public function afterIsApplicable(
        SpecificationInterface $subject,
        bool $result,
        MethodInterface $paymentMethod,
        Quote $quote
    ) {
        return $result || (!$result && $this->samplesChecker->isSamplesQuote($quote->getId()));
    }
}
