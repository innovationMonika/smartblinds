<?php declare(strict_types=1);

namespace Smartblinds\Magepack\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Store\Model\ScopeInterface;
use MageSuite\Magepack\Model\FileManager;

class BundlesLoader extends \MageSuite\Magepack\Block\BundlesLoader
{
    private const XML_PATH_ENABLE_MAGEPACK_PREFETCH = 'dev/js/enable_magepack_js_prefetch';

    private $scopeConfig;

    public function __construct(
        Context $context,
        DirectoryList $dir,
        FileManager $fileManager,
        PageConfig $pageConfig,
        RequireJsConfig $requireJsConfig,
        ScopeConfigInterface $scopeConfig,
        Minification $minification,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $context, $dir, $fileManager, $pageConfig,
            $requireJsConfig, $scopeConfig, $minification, $data
        );
    }

    public function isPrefetch(): bool
    {
        return $this->isEnabled() && $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_MAGEPACK_PREFETCH,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCommonBundleUrl(): string
    {
        $commonBundle = $this->getData('common_bundle');
        if ($commonBundle && $this->isPrefetch()) {
            return $this->getViewFileUrl($commonBundle['bundle_path']);
        }
        return '';
    }

    public function getPageBundlesUrls(): array
    {
        $pageBundles = $this->getData('page_bundles');
        if (!empty($pageBundles) && $this->isPrefetch()) {
            return array_map(function($pageBundle) {
                return $this->getViewFileUrl($pageBundle['bundle_path']);
            }, $pageBundles);
        }
        return [];
    }

    public function getPrefetchBundlesUrls(): array
    {
        $prefetchBundles = $this->getData('prefetch_bundles');
        if (!empty($prefetchBundles) && $this->isPrefetch()) {
            return array_values(
                array_map(function($prefetchBundle) {
                    return $this->getViewFileUrl($prefetchBundle);
                }, $prefetchBundles)
            );
        }
        return [];
    }
}
