<?php

namespace Smartblinds\Theme\Plugin\View\Page\Config;

use Magento\Framework\View\Page\Config;
use Smartblinds\Theme\Helper\Critical;

class HandleStylesAsset
{
    private Critical $criticalHelper;

    private $allStylesAdded = false;

    public function __construct(
        Critical $criticalHelper
    ) {
        $this->criticalHelper = $criticalHelper;
    }

    public function aroundAddPageAsset(
        Config $subject,
        $proceed,
        $file,
        array $properties = [],
        $name = null
    ) {
        if ($this->isStylesFile($file) && $this->criticalHelper->isCriticalPage()) {
            if ($file === 'css/styles-m.css') {
                return $proceed('css/styles-all.css', $properties, $name);
            }
            return $subject;
        }
        return $proceed($file, $properties, $name);
    }

    private function isStylesFile($file)
    {
        return in_array($file, [
            'css/styles-m.css',
            'css/styles-l.css'
        ]);
    }
}
