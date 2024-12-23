<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Model;

use RecursiveIterator;

class ImagesRecursiveFilterIterator extends \RecursiveFilterIterator
{
    /**
     * @var mixed
     */
    private $imageNeedSkip;

    /**
     * @var mixed
     */
    private $getWebPPath;

    /**
     * @var mixed
     */
    private $fileDrive;

    /**
     * @var mixed
     */
    private $checkNewerThan;

    /**
     * @var string[]
     */
    static $FILTERS = [
        'gif',
        'jpeg',
        'jpg',
        'png',
    ];

    /**
     * @return bool
     */
    public function accept(): bool
    {
        $current = $this->current();

        $isDir = $current->isDir();
        $result = $isDir || in_array(
            strtolower($current->getExtension()),
            self::$FILTERS,
            true
        );

        if ($result) {
            if (!$isDir) {

                $image = $current->getPathname();

                if ($this->getImageNeedSkip()->execute($image)) {
                    return false;
                }

                $webPImage = $this->getWebPPath()->execute($image);

                if ($this->getFileDrive()->isExists($webPImage) && !$this->getCheckNewerThan()->execute($image, $webPImage)) {
                    return false;
                }

                $skipDays = $this->getConfig()->getSkipByTime();

                if ($skipDays) {
                    $time = $current->getMTime();
                    if (($time > 0) && ($time < time() - $skipDays * 86400)) {
                        $result = false;
                    }
                }
            } else {
                if ('mf_webp' === $current->getFilename()) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    private function getConfig()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magefan\WebP\Model\Config::class);
    }

    /**
     * @return mixed
     */
    private function getImageNeedSkip()
    {
        if (null === $this->imageNeedSkip) {
            $this->imageNeedSkip = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magefan\WebP\Api\ImageNeedSkipInterface::class);
        }

        return $this->imageNeedSkip;
    }

    /**
     * @return mixed
     */
    private function getWebPPath()
    {
        if (null === $this->getWebPPath) {
            $this->getWebPPath = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magefan\WebP\Api\GetWebPPathInterface::class);
        }

        return $this->getWebPPath;
    }

    /**
     * @return mixed
     */
    private function getFileDrive()
    {
        if (null === $this->fileDrive) {
            $this->fileDrive = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Filesystem\Driver\File::class);
        }

        return $this->fileDrive;
    }

    /**
     * @return mixed
     */
    private function getCheckNewerThan()
    {
        if (null === $this->checkNewerThan) {
            $this->checkNewerThan = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magefan\WebP\Model\CheckNewerThan::class);
        }

        return $this->checkNewerThan;
    }
}
