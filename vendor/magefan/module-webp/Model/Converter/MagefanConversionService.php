<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Model\Converter;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\HTTP\Client\Curl;

class MagefanConversionService
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Magefan Conversion Service constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Curl $curl
     * @param Filesystem $filesystem
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Curl $curl,
        Filesystem $filesystem
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
        $this->filesystem = $filesystem;
    }

    /**
     * Convert image to webp
     * @param string $image
     * @param string $webpImage
     * @param int $quality
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function convert(string $image, string $webpImage, int $quality): bool
    {
        $key = $this->scopeConfig->getValue(
            'mfwebp/general/' . 'key',
            ScopeInterface::SCOPE_STORE
        );

        if (!$key) {
            return false;
        }

        $imageContent = file_get_contents($image);
        if (!$imageContent) {
            return false;
        }

        $curl = clone $this->curl;
        $curl ->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $curl ->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $curl ->post(
            'https://webpconverter.magefan.com/index.php',
            [
                'v'     => '1.0.0',
                'image' => $imageContent,
                'quality' => $quality,
                'key' => $key
            ]
        );

        $response = $curl ->getBody();
        if ($response) {
            $response = json_decode($response, true);
            if (!empty($response['success']) && !empty($response['content'])) {
                $content = base64_decode($response['content']);

                if ($content) {
                    $media = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                    $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
                    $media->writeFile(str_replace($mediaPath, '', $webpImage), $content);
                    return true;
                }
            }
        }

        return false;
    }
}
