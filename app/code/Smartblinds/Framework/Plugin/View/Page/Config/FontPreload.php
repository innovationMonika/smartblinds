<?php

namespace Smartblinds\Framework\Plugin\View\Page\Config;

use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Page\Config as PageConfig;

class FontPreload
{
    private $assetRepo;

    public function __construct(
        Repository $assetRepo
    ) {
        $this->assetRepo = $assetRepo;
    }

    public function afterGetIncludes(PageConfig $subject, $result)
    {
        return $result;

        $url = $this->assetRepo->getUrl('fonts/tstar-pro/regular/T-StarPRO-Regular.woff2');
        return $result . '<link rel="preload"
              href="' . $url . '"
              as="font" type="font/woff2" crossorigin />';
    }
}
