<?php declare(strict_types=1);

namespace GoMage\Samples\Plugin\Directory\Helper\Data;

use GoMage\Samples\Model\Claim\PlaceOrder\SamplesChecker;
use Magento\Directory\Helper\Data;

class PreventRequiredRegionForSamples
{
    private SamplesChecker $samplesChecker;

    public function __construct(SamplesChecker $samplesChecker)
    {
        $this->samplesChecker = $samplesChecker;
    }

    public function afterIsRegionRequired(Data $subject, bool $result)
    {
        return $result && !$this->samplesChecker->hasSamplesEntities();
    }
}
