<?php declare(strict_types=1);

namespace Smartblinds\Theme\Plugin\Block\Html\Header\CriticalCss;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\File\NotFoundException;
use Magento\Framework\View\Asset\Repository;
use Magento\Theme\Block\Html\Header\CriticalCss;
use Smartblinds\Theme\Helper\Critical;

class OverrideCss
{
    private Repository $assetRepo;
    private Critical $criticalHelper;

    public function __construct(
        Repository $assetRepo,
        Critical $criticalHelper
    ) {
        $this->assetRepo = $assetRepo;
        $this->criticalHelper = $criticalHelper;
    }

    public function afterGetCriticalCssData(
        CriticalCss $subject,
        $result
    ) {
        if (!$this->criticalHelper->isCriticalPage()) {
            return '';
        }

        try {
            $asset = $this->assetRepo->createAsset($this->criticalHelper->getCriticalFile(), ['_secure' => 'false']);
            $content = $asset->getContent();
        } catch (LocalizedException | NotFoundException $e) {
            $content = '';
        }

        return $content;
    }
}
