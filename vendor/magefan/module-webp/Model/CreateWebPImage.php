<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Model;

use Magento\Framework\Filesystem\DirectoryList;
use WebPConvert\WebPConvert;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magefan\WebP\Model\Converter\MagefanConversionService;
use Magefan\WebP\Model\Converter\Gifsicle;
use Magefan\WebP\Model\Config\Source\CreationOptions;
use Magefan\WebP\Model\Config;

use Magefan\WebP\Api\GetWebPUrlInterface;
use Magefan\WebP\Api\CreateWebPImageInterface;
use Magefan\WebP\Api\ImageNeedSkipInterface;
use Magefan\WebP\Model\CheckNewerThan;

/**
 * Methods to convert images to WebP
 */
class CreateWebPImage implements CreateWebPImageInterface
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

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
     * @var Gifsicle
     */
    private $gifsicle;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var GetWebPUrlInterface
     */
    private $getWebPUrl;

    /**
     * @var ImageNeedSkipInterface
     */
    private $imageNeedSkip;

    /**
     * @var CheckNewerThan
     */
    private $checkNewerThan;

    /**
     * @param DirectoryList $directoryList
     * @param File $fileDriver
     * @param StoreManagerInterface $storeManager
     * @param AssetRepository $assetRepository
     * @param MagefanConversionService $magefanConversionService
     * @param Gifsicle $gifsicle
     * @param \Magefan\WebP\Model\Config $config
     * @param GetWebPUrlInterface $getWebPUrl
     * @param ImageNeedSkipInterface $imageNeedSkip
     * @param CheckNewerThan $checkNewerThan
     */
    public function __construct(
        DirectoryList $directoryList,
        File $fileDriver,
        StoreManagerInterface $storeManager,
        AssetRepository $assetRepository,
        MagefanConversionService $magefanConversionService,
        Gifsicle $gifsicle,
        Config $config,
        GetWebPUrlInterface $getWebPUrl,
        ImageNeedSkipInterface $imageNeedSkip = null,
        CheckNewerThan $checkNewerThan = null
    ) {
        $this->directoryList = $directoryList;
        $this->rootFolder = $this->directoryList->getRoot();
        $this->pubFolder = $this->rootFolder . '/pub';
        $this->fileDriver = $fileDriver;
        $this->storeManager = $storeManager;
        $this->assetRepository = $assetRepository;
        $this->magefanConversionService = $magefanConversionService;
        $this->gifsicle = $gifsicle;
        $this->config = $config;
        $this->getWebPUrl = $getWebPUrl;
        $this->imageNeedSkip = $imageNeedSkip ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ImageNeedSkipInterface::class);
        $this->checkNewerThan = $checkNewerThan ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(CheckNewerThan::class);
        $this->stopConverting = false;
    }

    /**
     * Convert image to WebP using its URL. Return true if converted successfully or WebP image already exists.
     * @param string $imageUrl
     * @param int|null $mode
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(string $imageUrl, ?int $mode = null): bool
    {
        if (null === $mode) {
            $mode = CreationOptions::PAGE_LOAD;
        }

        $imageUrl = explode('?', $imageUrl)[0];

        $originImageUrl = $imageUrl;
        $isExternalWebsite = false;

        foreach ($this->config->getExternalWebsites() as $externalWebsite) {
            if (strpos($imageUrl, $externalWebsite) !== false) {
                $imageUrl = str_replace($externalWebsite, $this->getMediaBaseUrl(), $imageUrl);
                $isExternalWebsite = true;
                break;
            }
        }

        $image = ($mode == CreationOptions::PAGE_LOAD) ? $this->getPathFromUrl($imageUrl) : $imageUrl;

        if ($this->imageNeedSkip->execute($image)) {
            return false;
        }

        if (!$this->fileDriver->isExists($image)) {

            if ($isExternalWebsite) {
                $content = file_get_contents($originImageUrl);
                if (!$this->fileDriver->isExists(dirname($image))) {
                    mkdir(dirname($image), 0775, true);
                }

                if ($content) {
                    file_put_contents($image, $content);
                } else {
                    $content = imagecreatetruecolor(1, 1);
                    imagejpeg($content, $image);
                    imagedestroy($content);
                }

                if (!$this->fileDriver->isExists($image)) {
                    return false;
                }

            } else {
                return false;
            }
        }

        $webpImageUrl = $this->getWebPUrl->execute($imageUrl);
        $webpImage = $this->getPathFromUrl($webpImageUrl);

        if ($this->fileDriver->isExists($webpImage) && !$this->checkNewerThan->execute($image, $webpImage)) {
            return true;
        }

        if ($this->stopConverting) {
            return false;
        }

        if ($mode == CreationOptions::PAGE_LOAD
            && in_array(
                $this->config->getGenerationOption(),
                [CreationOptions::CRON, CreationOptions::MANUAL]
            )) {
            return false;
        }

        $quality = $this->config->getQuality();

        try {
            if ($this->config->useMagefanConversionService()) {
                /* Use Magefan WebP Conversion Service */
                /* To enable need to
                    1. add to env.php

                    'system' => [
                        'default' => [
                            'mfwebp' => [
                                'general' => [
                                    'magefan_conversion_service' => '1'
                                ]
                            ]
                        ]
                    ]

                    2. Contact Magefan to enable Conversion Service for your account
                    3. Run bin/magento app:config:import
                */
                return $this->magefanConversionService->convert($image, $webpImage, $quality);
            } else {
                if (preg_match('/\.(gif|GIF)$/i', $image)) {
                    return $this->gifsicle->convert($image, $webpImage, $quality);
                } else {
                    WebPConvert::convert(
                        $image,
                        $webpImage,
                        [
                            'quality' => $quality,
                            'max-quality' => $quality,
                            'converters' => ['cwebp', 'gd', 'imagick', 'wpc', 'ewww'],
                            'metadata' => $this->config->getImageMetadata(),
                            //'size-in-percentage' => 90
                        ]
                    );

                    return true;
                }
            }
        } catch (\Exception $e) {
            if ($this->config->useMagefanConversionService()) {
                $this->stopConverting = true;
            }
        }

        return false;
    }

    /**
     * Return image location on server
     * @param string $image
     * @return string
     */
    private function getPathFromUrl(string $imageUrl): string
    {
        $imageUrl = trim($imageUrl);
        $imageUrl = str_replace('%20', ' ', $imageUrl);

        $mediaBaseUrl = $this->getMediaBaseUrl();
        $staticBaseUrl = $this->getStaticBaseUrl();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $baseUrl = str_replace('/' . $this->storeManager->getStore()->getCode() . '/', '/', $baseUrl);

        if (strlen($imageUrl) > 1
            && $imageUrl[0] == '/'
            && $imageUrl[1] != '/'
            && false === strpos($imageUrl, $mediaBaseUrl)
            && false === strpos($imageUrl, $staticBaseUrl)
        ) {
            /* Case when url defined without donain e.g. "/pub/media/wysiwyg/article_images/blue_bird.jpg" */
            $rootFolder = (strpos($imageUrl, '/pub/') === 0) ? $this->rootFolder : $this->pubFolder;
            $imagePath = $rootFolder . $imageUrl;
        } else {

            $imagePath = str_replace(
                [
                    $mediaBaseUrl,
                    $staticBaseUrl,
                    $baseUrl . 'pub/media/',
                    $baseUrl . 'pub/static/',
                    $baseUrl . 'media/',
                    $baseUrl . 'static/'
                ],
                [
                    $this->pubFolder . '/media/',
                    $this->pubFolder . '/static/',
                    $this->pubFolder . '/media/',
                    $this->pubFolder . '/static/',
                    $this->pubFolder . '/media/',
                    $this->pubFolder . '/static/'
                ],
                $imageUrl
            );
        }

        $imagePath = urldecode($imagePath);

        return $imagePath;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getMediaBaseUrl(): string
    {
        if (null === $this->mediaBaseUrl) {
            $this->mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );
        }

        return $this->mediaBaseUrl;
    }

    /**
     * @return string
     */
    private function getStaticBaseUrl(): string
    {
        if (null === $this->staticBaseUrl) {
            $staticBaseUrl = $this->assetRepository->getUrl('');
            $staticBaseUrl = explode('/', $staticBaseUrl);
            $staticBaseUrl = array_slice($staticBaseUrl, 0, count($staticBaseUrl) - 4);
            $staticBaseUrl = implode('/', $staticBaseUrl) . '/';

            $this->staticBaseUrl = $staticBaseUrl;
        }

        return $this->staticBaseUrl;
    }
}
