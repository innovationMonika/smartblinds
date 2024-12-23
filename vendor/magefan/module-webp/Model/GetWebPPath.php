<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\WebP\Model;

use Magefan\WebP\Api\GetWebPPathInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\Filesystem\DirectoryList;

class GetWebPPath implements GetWebPPathInterface
{
    /**
     * @var string
     */
    private $mediaBaseUrl;

    /**
     * @var string
     */
    private $staticBaseUrl;

    /**
     * @var string
     */
    private $pubFolder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param StoreManagerInterface $storeManager
     * @param AssetRepository $assetRepository
     * @param DirectoryList $directoryList
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        AssetRepository $assetRepository,
        DirectoryList $directoryList
    ) {
        $this->storeManager = $storeManager;
        $this->assetRepository = $assetRepository;
        $this->directoryList = $directoryList;
    }

    /**
     * @param string $image
     * @return string
     */
    public function execute(string $image): string
    {
        $imageFormat = substr($image, strrpos($image, ".") + 1);
        $mediaBaseUrl = $this->getMediaBaseUrl();
        $staticBaseUrl = $this->getStaticBaseUrl();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $baseUrl = str_replace('/' . $this->storeManager->getStore()->getCode() . '/', '/', $baseUrl);

        $imagePath = str_replace(
            [
                $mediaBaseUrl,
                $staticBaseUrl,
                $baseUrl . 'pub/media/',
                $baseUrl . 'pub/static/',
                $this->getPubFolder() . '/',
                $baseUrl . 'media/',
                $baseUrl . 'static/'
            ],
            [
                'media/',
                'static/',
                'media/',
                'static/',
                '',
                'media/',
                'static/'
            ],
            $image
        );

        $imagePath = preg_replace('/\.(jpg|jpeg|png|JPG|JPEG|PNG|gif|GIF)/i', '.webp', $imagePath);

        return $this->getPubFolder()  . '/media/mf_webp/' . $imageFormat  . '/' . $imagePath;
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

    /**
     * @return string
     */
    private function getPubFolder()
    {
        if (null === $this->pubFolder) {
            $this->pubFolder = $this->directoryList->getRoot() . '/pub';
        }

        return $this->pubFolder;
    }
}
