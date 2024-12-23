<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Model;

use Magefan\WebP\Api\ImageNeedSkipInterface;
use Magento\Framework\Filesystem\DirectoryList;

class ImageNeedSkip implements ImageNeedSkipInterface
{
    /**
     * @var \Magefan\WebP\Model\Config
     */
    private $config;

    /**
     * @var string
     */
    private $pubFolder = '';

    /**
     * @param \Magefan\WebP\Model\Config $config
     * @param DirectoryList $directoryList
     */
    public function __construct(
        Config $config,
        DirectoryList $directoryList
    ) {
        $this->config = $config;
        $this->pubFolder = $directoryList->getRoot() . '/pub';
    }

    public function execute(string $image): bool
    {
        /**
         * Check XML_PATH_EXTENSION_SKIP_MEDIA_FOLDERS on folder and image to skip
         */
        foreach ($this->config->getSkipFolders(Config::XML_PATH_EXTENSION_SKIP_MEDIA_FOLDERS) as $folder) {

            if ($this->endsWith($image, trim($folder))) {
                return true;
            }

            $pathToFolder = $this->pubFolder . '/' . trim(trim($folder), '/') . '/';

            if (strpos($image, $pathToFolder) === 0) {
                return true;
            }
        }

        /**
         * Check XML_PATH_EXTENSION_SKIP_STATIC_FOLDERS on folder to skip
         */
        if (strpos($image, $this->pubFolder . '/static/') === 0) {
            foreach ($this->config->getSkipFolders(Config::XML_PATH_EXTENSION_SKIP_STATIC_FOLDERS) as $folder) {
                $pathToFolder =  '/' . trim(trim($folder), '/') . '/';

                $pos = strpos($image, $pathToFolder);
                if (false !== $pos && $pos > 0) {
                    return true;
                }
            }
        }

        /**
         * Check XML_PATH_EXTENSION_SKIP_STATIC_FOLDERS on images to skip
         */
        foreach ($this->config->getSkipFolders(Config::XML_PATH_EXTENSION_SKIP_STATIC_FOLDERS) as $folder) {

            if ($this->endsWith($image, trim($folder))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return true if $string ends with $endString
     * @param string $string
     * @param string $endString
     * @return bool
     */
    private function endsWith(string $string, string $endString): bool
    {
        $len = strlen($endString);

        if ($len < 3) {
            return false;
        }

        return (bool) (substr($string, -$len) === $endString);
    }
}
