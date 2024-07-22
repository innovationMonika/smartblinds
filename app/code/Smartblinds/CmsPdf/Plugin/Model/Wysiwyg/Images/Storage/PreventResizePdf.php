<?php

namespace Smartblinds\CmsPdf\Plugin\Model\Wysiwyg\Images\Storage;

use Magento\Cms\Model\Wysiwyg\Images\Storage;

class PreventResizePdf
{
    public function aroundResizeFile(
        Storage $subject,
        callable $proceed,
        $source,
        $keepRatio = true
    ) {
        if ($this->endsWith($source, '.pdf')) {
            return true;
        }
        return $proceed($source, $keepRatio);
    }

    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if (!$length) {
            return true;
        }
        return substr($haystack, -$length) === $needle;
    }
}
