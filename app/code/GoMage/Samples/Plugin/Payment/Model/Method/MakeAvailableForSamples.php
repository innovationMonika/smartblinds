<?php declare(strict_types=1);

namespace GoMage\Samples\Plugin\Payment\Model\Method;

use GoMage\Samples\Model\Claim\PlaceOrder\SamplesChecker;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;

class MakeAvailableForSamples
{
    private SamplesChecker $samplesChecker;

    public function __construct(SamplesChecker $samplesChecker)
    {
        $this->samplesChecker = $samplesChecker;
    }

    public function afterIsAvailable(
        MethodInterface $subject,
        bool $result,
        CartInterface $quote = null
    ) {
        return $result || (!$result && $this->samplesChecker->isSamplesQuote($quote->getId()));
    }
}
