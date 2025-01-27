<?php

namespace Smartblinds\Options\Plugin\MageWorx\OptionFeatures\Plugin\AroundGetOptionPrice;

use MageWorx\OptionFeatures\Plugin\AroundGetOptionPrice;

class PreventQtys
{
    public function aroundAroundGetEditableOptionValue(
        AroundGetOptionPrice $subject,
        callable $proceed,
        $originalSubject,
        $originalProceed,
        $optionValue
    ) {
        return $originalProceed($optionValue);
    }
}
