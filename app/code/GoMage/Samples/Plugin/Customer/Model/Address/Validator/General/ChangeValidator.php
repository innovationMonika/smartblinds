<?php declare(strict_types=1);

namespace GoMage\Samples\Plugin\Customer\Model\Address\Validator\General;

use GoMage\Samples\Model\Claim\PlaceOrder\Address\Validator\General as SamplesGeneralValidator;
use GoMage\Samples\Model\Claim\PlaceOrder\SamplesChecker;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Address\Validator\General;

class ChangeValidator
{
    private SamplesChecker $samplesChecker;
    private SamplesGeneralValidator $validator;

    public function __construct(
        SamplesChecker $samplesChecker,
        SamplesGeneralValidator $validator
    ) {
        $this->samplesChecker = $samplesChecker;
        $this->validator = $validator;
    }

    public function aroundValidate(
        General $subject,
        callable $proceed,
        AbstractAddress $address
    ) {
        $quote = $address->getQuote();
        if (!$quote) {
            return $proceed($address);
        }

        if ($this->samplesChecker->isSamplesQuote($quote->getId())) {
            return $this->validator->validate($address);
        }

        return $proceed($address);
    }
}
