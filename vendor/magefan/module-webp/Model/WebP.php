<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\WebP\Model;

use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use WebPConvert\WebPConvert;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magefan\WebP\Model\Config\Source\CreationOptions;
use Magento\Framework\Shell;

use Magefan\WebP\Api\CreateWebPImageInterface;
use Magefan\WebP\Api\GetWebPUrlInterface;
use Magefan\WebP\Api\GetOriginWebPUrlInterface;
use Magefan\WebP\Api\HtmlParserInterface;

/**
 * Methods to convert images to WebPe
 * @deprecated 2.1.0 Use API methods instead
 */
class WebP
{
    const XML_PATH_EXTENSION_ENABLED                = 'mfwebp/general/enabled';
    const XML_PATH_EXTENSION_IMAGE_QUALITY          = 'mfwebp/general/quality';
    const XML_PATH_EXTENSION_MAGEFAN_CONVERSION_SERVICE = 'mfwebp/general/magefan_conversion_service';
    const XML_PATH_EXTENSION_SKIP_FOLDERS           = 'mfwebp/general/skip_folders';
    const XML_PATH_EXTENSION_SKIP_MEDIA_FOLDERS     = 'mfwebp/general/skip_media_folders';
    const XML_PATH_EXTENSION_SKIP_STATIC_FOLDERS    = 'mfwebp/general/skip_static_folders';
    const XML_PATH_EXTENSION_WHEN_GENERATE_WEBP     = 'mfwebp/general/creation_options';

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var
     */
    private $file;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $pubFolder = '';

    /**
     * @var string
     */
    private $rootFolder = '';

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var array
     */
    private $webpUrls = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    /**
     * @var string
     */
    private $mediaBaseUrl;

    /**
     * @var string
     */
    private $staticBaseUrl;

    /**
     * @var bool
     */
    private $stopConverting;

    /**
     * @var MagefanConversionService
     */
    private $magefanConversionService;

    /**
     * @var Shell
     */
    private $shell;

    /**
     * WebP constructor.
     * @param DirectoryList $directoryList
     * @param ReadFactory $readFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param File $fileDriver
     * @param StoreManagerInterface $storeManager
     * @param AssetRepository $assetRepository
     * @param $magefanConversionService
     * @param Shell|null $shell
     * @param CreateWebPImageInterface|null $createWebPImage
     * @param GetWebPUrlInterface|null $getWebPUrl
     * @param GetOriginWebPUrlInterface|null $getOriginWebPUrl
     * @param HtmlParserInterface|null $htmlParser
     */
    public function __construct(
        DirectoryList $directoryList,
        ReadFactory $readFactory,
        ScopeConfigInterface $scopeConfig,
        File $fileDriver,
        StoreManagerInterface $storeManager,
        AssetRepository $assetRepository,
        $magefanConversionService = null,
        Shell $shell = null,
        CreateWebPImageInterface $createWebPImage = null,
        GetWebPUrlInterface $getWebPUrl = null,
        GetOriginWebPUrlInterface $getOriginWebPUrl = null,
        HtmlParserInterface $htmlParser = null
    ) {
        $this->directoryList = $directoryList;
        $this->readFactory = $readFactory;
        $this->scopeConfig = $scopeConfig;
        $this->rootFolder = $this->directoryList->getRoot();
        $this->pubFolder = $this->rootFolder . '/pub';

        $this->fileDriver = $fileDriver;
        $this->storeManager = $storeManager;
        $this->assetRepository = $assetRepository;
        $this->stopConverting = false;
        $this->shell = $shell;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->magefanConversionService = $magefanConversionService;

        $this->shell = $shell ?: $objectManager->get(\Magento\Framework\Shell::class);

        $this->createWebPImage = $createWebPImage ?: $objectManager->get(CreateWebPImageInterface::class);
        $this->getWebPUrl = $getWebPUrl ?: $objectManager->get(GetWebPUrlInterface::class);
        $this->getOriginWebPUrl = $getWebPUrl ?: $objectManager->get(GetOriginWebPUrlInterface::class);
        $this->htmlParser = $htmlParser ?: $objectManager->get(HtmlParserInterface::class);
    }

    /**
     * Retrieve true if webp module is enabled
     * @param null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (bool)$this->getConfig(
            self::XML_PATH_EXTENSION_ENABLED,
            $storeId
        );
    }

    /**
     * Return Picture Tag (Html) with original and webp images
     * @param $imageUrl
     * @param $htmlTag
     * @return string
     */
    public function getPictureTagHtml($imageUrl, $htmlTag)
    {
        return $this->htmlParser->getNewHtmlTag($imageUrl, $htmlTag);
    }

    /**
     * Parse HTML and replace regular images with WebP
     * @param string $output
     * @return string
     */
    public function parseHtml($output)
    {
        return $this->htmlParser->execute($output);
    }

    /**
     * @param string $imageUrl
     * @param string $mode
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function convert(string $imageUrl, $mode = null)
    {
        return $this->createWebPImage->execute($imageUrl, $mode);
    }

    /**
     * @param string $imageUrl
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebpUrl(string $imageUrl)
    {
        return $this->getWebPUrl->execute($imageUrl);
    }

    /**
     * @param string $webPUrl
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOriginWebPImage(string $webPUrl)
    {
        return $this->getOriginWebPUrl->execute($webPUrl);
    }

    /**
     * @return mixed
     */
    public function getGenerationOption()
    {
        return $this->getConfig(self::XML_PATH_EXTENSION_WHEN_GENERATE_WEBP);
    }

    /**
     * Retrieve store config value
     * @param string $path
     * @param null $storeId
     * @return mixed
     */
    private function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
