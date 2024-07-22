<?php

namespace Smartblinds\RequireJs\Block\Html\Head;

use Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Framework\View\Asset\ConfigInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\RequireJs\Model\FileManager;

class Config extends \Magento\RequireJs\Block\Html\Head\Config
{
    private $fileManager;
    private $bundleConfig;

    public function __construct(
        Context $context,
        RequireJsConfig $config,
        FileManager $fileManager,
        PageConfig $pageConfig,
        ConfigInterface $bundleConfig,
        Minification $minification,
        array $data = []
    ) {
        parent::__construct(
            $context, $config, $fileManager, $pageConfig,
            $bundleConfig, $minification, $data
        );
        $this->fileManager = $fileManager;
        $this->bundleConfig = $bundleConfig;
    }

    protected function _prepareLayout()
    {
        $after = RequireJsConfig::REQUIRE_JS_FILE_NAME;
        $assetCollection = $this->pageConfig->getAssetCollection();
        if ($this->minification->isEnabled('js')) {
            $minResolver = $this->fileManager->createMinResolverAsset();
            $assetCollection->insert(
                $minResolver->getFilePath(),
                $minResolver,
                $after
            );
            $after = $minResolver->getFilePath();
        }
        $requireJsMapConfig = $this->fileManager->createRequireJsMapConfigAsset();
        if ($requireJsMapConfig) {
            $urlResolverAsset = $this->fileManager->createUrlResolverAsset();
            $assetCollection->insert(
                $urlResolverAsset->getFilePath(),
                $urlResolverAsset,
                $after
            );
            $after = $urlResolverAsset->getFilePath();
            $assetCollection->insert(
                $requireJsMapConfig->getFilePath(),
                $requireJsMapConfig,
                $after
            );
            $after = $requireJsMapConfig->getFilePath();
        }
        if ($this->bundleConfig->isBundlingJsFiles()) {
            $bundleAssets = $this->fileManager->createBundleJsPool();
            $staticAsset = $this->fileManager->createStaticJsAsset();
            /** @var File $bundleAsset */
            if (!empty($bundleAssets) && $staticAsset !== false) {
                $bundleAssets = array_reverse($bundleAssets);
                foreach ($bundleAssets as $bundleAsset) {
                    $assetCollection->insert(
                        $bundleAsset->getFilePath(),
                        $bundleAsset,
                        $after
                    );
                }
                $assetCollection->insert(
                    $staticAsset->getFilePath(),
                    $staticAsset,
                    reset($bundleAssets)->getFilePath()
                );
                $after = $staticAsset->getFilePath();
            }
        }
        $requireJsMixinsConfig = $this->fileManager->createRequireJsMixinsAsset();
        $assetCollection->insert(
            $requireJsMixinsConfig->getFilePath(),
            $requireJsMixinsConfig,
            $after
        );
        $requireJsConfig = $this->fileManager->createRequireJsConfigAsset();
        $assetCollection->insert(
            $requireJsConfig->getFilePath(),
            $requireJsConfig,
            $after
        );
        return AbstractBlock::_prepareLayout();
    }
}
