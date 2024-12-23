<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use Magefan\WebP\Model\BrowserDetector;

class Config
{

    const XML_PATH_EXTENSION_ENABLED                    = 'mfwebp/general/enabled';
    const XML_PATH_EXTENSION_IMAGE_QUALITY              = 'mfwebp/general/quality';
    const XML_PATH_EXTENSION_IMAGE_METADATA             = 'mfwebp/general/metadata';
    const XML_PATH_EXTENSION_MAGEFAN_CONVERSION_SERVICE = 'mfwebp/general/magefan_conversion_service';
    const XML_PATH_EXTENSION_SKIP_BY_TIME               = 'mfwebp/general/skip_by_time';
    const XML_PATH_EXTENSION_SKIP_MEDIA_FOLDERS         = 'mfwebp/general/skip_media_folders';
    const XML_PATH_EXTENSION_SKIP_STATIC_FOLDERS        = 'mfwebp/general/skip_static_folders';
    const XML_PATH_EXTENSION_WHEN_GENERATE_WEBP         = 'mfwebp/general/creation_options';
    const XML_PATH_EXTENSION_CONVERT_EXISTS_PICTURE     = 'mfwebp/advanced_settings/convert_exists_picture';
    const XML_PATH_EXTENSION_CONVERT_BACKGROUND_IMAGES  = 'mfwebp/advanced_settings/convert_background_images';
    const XML_PATH_EXTENSION_CONVERT_IMAGE_IN_GRAPHQL   = 'mfwebp/advanced_settings/convert_images_in_graphql_requests';
    const XML_PATH_EXTENSION_CONVERT_IMAGE_IN_REST_API  = 'mfwebp/advanced_settings/convert_images_in_rest_api_requests';
    const XML_PATH_EXTENSION_EXTERNAL_WEBSITES          = 'mfwebp/advanced_settings/external_websites';
    const XML_PATH_EXTENSION_CONVERT_IMAGES_INSERTED_VIA_PAGE_BUILDER = 'mfwebp/advanced_settings/convert_images_inserted_via_page_builder';

    const PICTURE_WITH_MFWEBP_CLASS                 = '<picture class="mfwebp">';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var bool
     */
    private $browserWebPCompatible;

    /**
     * @var array
     */
    private $skipFolders;

    /**
     * @var array
     */
    private $externalWebsites;

    /**
     * @var BrowserDetector
     */
    private $browserDetector;

    /**
     * @var string
     */
    private $imageMetadata;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\App\RequestInterface::class);
    }

    /**
     * Retrieve true if webp module is enabled
     * @param null|int|string $storeId
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        return (bool) $this->getConfig(
            self::XML_PATH_EXTENSION_ENABLED,
            $storeId
        );
    }

    /**
     * Retrieve store config value
     * @param string $path
     * @param null|int|string $storeId
     * @return mixed
     */
    private function getConfig(string $path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return quality for convert
     * @param null|int|string $storeId
     * @return int
     */
    public function getQuality($storeId = null): int
    {
        return (int) $this->getConfig(
            self::XML_PATH_EXTENSION_IMAGE_QUALITY,
            $storeId
        );
    }

    /**
     * Return image metadata
     * @param null|int|string $storeId
     * @return string
     */
    public function getImageMetadata($storeId = null): string
    {
        if (null === $this->imageMetadata) {
            $metadata = (string)$this->getConfig(
                self::XML_PATH_EXTENSION_IMAGE_METADATA,
                $storeId
            );

            $validItems = [];
            $items = explode(',', $metadata);
            foreach ($items as $item) {
                $item = trim($item);
                if (in_array($item, ['all', 'none', 'exif', 'icc', 'xmp'])) {
                    $validItems[] = $item;
                }
            }

            if (!count($validItems)) {
                $validItems[] = 'none';
            }

            $this->imageMetadata = implode(',', $validItems);
        }

        return $this->imageMetadata;
    }

    /**
     * Return quality for convert
     * @param null|int|string $storeId
     * @return int
     */
    public function useMagefanConversionService($storeId = null): int
    {
        return (int) $this->getConfig(
            self::XML_PATH_EXTENSION_MAGEFAN_CONVERSION_SERVICE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return int
     */
    public function getSkipByTime($storeId = null): int
    {
        return (int) $this->getConfig(self::XML_PATH_EXTENSION_SKIP_BY_TIME, $storeId);
    }

    /**
     * Return list of folders that shouldn't be converted
     * @param string $folderType
     * @param null|int|string $storeId
     * @return array
     */
    public function getSkipFolders(string $folderType, $storeId = null): array
    {
        $key = $folderType . ((string)$storeId);
        if (!isset($this->skipFolders[$key])) {
            $this->skipFolders[$key] = [];
            $skipFolders = (string) $this->getConfig($folderType, $storeId);
            $skipFolders = str_replace("\r", "\n", trim($skipFolders));
            $skipFolders = explode("\n", $skipFolders);
            foreach ($skipFolders as $value) {
                $value = trim($value);
                if ($value) {
                    $this->skipFolders[$key][] = $value;
                }
            }
        }

        return $this->skipFolders[$key];
    }

    /**
     * Return list of External Websites, images from that shouldn't be converted
     * @param null|int|string $storeId
     * @return array
     */
    public function getExternalWebsites($storeId = null): array
    {
        if (null === $this->externalWebsites) {
            $skipFolders = (string) $this->getConfig(self::XML_PATH_EXTENSION_EXTERNAL_WEBSITES);
            $skipFolders = str_replace("\r", "\n", trim($skipFolders));
            $this->externalWebsites = explode("\n", $skipFolders);
            foreach ($this->externalWebsites as $k => $v) {
                $v = trim($v);
                if ($v) {
                    $this->externalWebsites[$k] = $v;
                } else {
                    unset($this->externalWebsites[$k]);
                }
            }
        }

        return $this->externalWebsites;
    }

    /**
     * @return mixed
     */
    public function getGenerationOption()
    {
        return $this->getConfig(self::XML_PATH_EXTENSION_WHEN_GENERATE_WEBP);
    }

    /**
     * Retrieve true if option is enabled
     * @param null|int|string $storeId
     * @return bool
     */
    public function convertExistingPictureTag($storeId = null): bool
    {
        return (bool) $this->getConfig(
            self::XML_PATH_EXTENSION_CONVERT_EXISTS_PICTURE,
            $storeId
        );
    }

    /**
     * Retrieve true if option is enabled
     * @param null|int|string $storeId
     * @return bool
     */
    public function convertBackgroundImages($storeId = null): bool
    {
        return (bool) $this->getConfig(
            self::XML_PATH_EXTENSION_CONVERT_BACKGROUND_IMAGES,
            $storeId
        );
    }

    /**
     * Retrieve true if option is enabled
     * @param null|int|string $storeId
     * @return bool
     */
    public function convertImagesInsertedViaPageBuilder($storeId = null): bool
    {
        return (bool) $this->getConfig(
            self::XML_PATH_EXTENSION_CONVERT_IMAGES_INSERTED_VIA_PAGE_BUILDER,
            $storeId
        );
    }

    /**
     * Retrieve true if browser is compatible with WebP
     * @return bool
     */
    public function isBrowserWebPCompatible():bool
    {
        if (null === $this->browserWebPCompatible) {

            $this->browserWebPCompatible = true;
            $browserDetector = $this->getBrowserDetector();
            $browser = $browserDetector->getBrowser();
            $version = $browserDetector->getVersion();

            if (in_array($browser, [BrowserDetector::BROWSER_SAFARI,  BrowserDetector::BROWSER_IPHONE, BrowserDetector::BROWSER_IPAD ])
                && $version < 14
            ) {
                $this->browserWebPCompatible = false;
            }

            if ($browser == BrowserDetector::BROWSER_EDGE && $version >= 18) {
                if (strpos($browserDetector->getVersion(), '.')) {
                    if (explode('.', $version)[0] < 18) {
                        $this->browserWebPCompatible = false;
                    }
                }
            }
        }

        return $this->browserWebPCompatible;
    }

    /**
     * Retrieve Browser Detector object
     * @return BrowserDetector
     */
    private function getBrowserDetector(): BrowserDetector
    {
        if (null === $this->browserDetector) {
            $this->browserDetector = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(BrowserDetector::class);
        }

        return $this->browserDetector;
    }

    /**
     * @param null|int|string $storeId
     * @return bool
     */
    public function isConvertImagesInGraphQLRequests($storeId = null): bool
    {
        return (bool) $this->getConfig(
            self::XML_PATH_EXTENSION_CONVERT_IMAGE_IN_GRAPHQL,
            $storeId
        );
    }

    /**
     * @param null|int|string $storeId
     * @return bool
     */
    public function isConvertImagesInRestApiRequests($storeId = null): bool
    {
        return (bool) $this->getConfig(
            self::XML_PATH_EXTENSION_CONVERT_IMAGE_IN_REST_API,
            $storeId
        );
    }
}
