<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\WebP\Model;

use Magefan\WebP\Api\GetWebPUrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\Filesystem\DirectoryList;
use Magefan\WebP\Model\Config;

/**
 * Retrive WebP image URL using original png,jpg,gif URL
 */
class GetWebPUrl implements GetWebPUrlInterface
{
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
     * @var array
     */
    private $webpUrls = [];

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
     * @var Config
     */
    private $config;

    /**
     * GetWebPUrl constructor.
     * @param StoreManagerInterface $storeManager
     * @param AssetRepository $assetRepository
     * @param DirectoryList $directoryList
     * @param Config|null $config
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        AssetRepository $assetRepository,
        DirectoryList $directoryList,
        Config $config = null
    ) {
        $this->storeManager = $storeManager;
        $this->assetRepository = $assetRepository;
        $this->directoryList = $directoryList;
        $this->config = $config ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magefan\WebP\Model\Config::class
            );
    }

    /**
     * Retrive WebP image URL
     * @param string $imageUrl
     * @return string
     */
    public function execute(string $imageUrl): string
    {
        if (!isset($this->webpUrls[$imageUrl])) {

            $imageParrams = explode('?', $imageUrl);
            $imageUrl = $imageParrams[0];
            $getParrams = (isset($imageParrams[1])) ? '?' . $imageParrams[1] : '';

            foreach ($this->config->getExternalWebsites() as $externalWebsite) {
                if (strpos($imageUrl, $externalWebsite) !== false) {
                    $originImageUrl = $imageUrl;
                    $imageUrl = str_replace($externalWebsite, $this->getMediaBaseUrl(), $imageUrl);
                    break;
                }
            }

            $imageUrl = trim($imageUrl);
            $imageFormat = substr($imageUrl, strrpos($imageUrl, ".") + 1);
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
                $imageUrl
            );

            $imagePath = preg_replace('/\.(jpg|jpeg|png|JPG|JPEG|PNG|gif|GIF)/i', '.webp', $imagePath);
            $imagePath = $mediaBaseUrl . 'mf_webp/' . $imageFormat  . '/' . $imagePath;
            $imagePath = str_replace(' ', '%20', $imagePath);
            $this->webpUrls[$imageUrl] = $imagePath . $getParrams;
        }

        return $this->webpUrls[$imageUrl];
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
