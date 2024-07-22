<?php

namespace Smartblinds\Options\Plugin\MageWorx\OptionBase\Plugin\AroundOptionsHtml;

use Magento\Catalog\Block\Product\View\Options;
use Magento\Catalog\Model\Product\Option;
use MageWorx\OptionBase\Plugin\AroundOptionsHtml;
use Smartblinds\Options\Model\Product\Option\Type\CurtainTracksWidth;

class IgnoreCurtainsTrackWidth
{
    public function aroundAroundGetOptionHtml(
        AroundOptionsHtml $subject,
        callable $proceed,
        Options $pluginSubject,
        \Closure $pluginProceed,
        Option $option
    ) {
        if ($option->getType() === CurtainTracksWidth::TYPE_CODE) {
            return $pluginProceed($option);
        }
        return $proceed($pluginSubject, $pluginProceed, $option);
    }
}
