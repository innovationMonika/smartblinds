<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);
namespace Magefan\WebP\Model\Filesystem;

use Magefan\WebP\Api\CreateWebPImageInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Phrase;

class PubFolder
{
    /**
     * @var CreateWebPImageInterface
     */
    private $createWebPImage;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IoFile
     */
    private $file;

    /**
     * @var Filesystem\Directory\ReadInterface
     */
    private $pub;

    /**
     * Folder constructor.
     * @param WebP $webp
     * @param Filesystem $filesystem
     * @param IoFile $file
     */
    public function __construct(
        CreateWebPImageInterface $createWebPImage,
        Filesystem $filesystem,
        IoFile $file
    ) {
        $this->createWebPImage = $createWebPImage;
        $this->filesystem = $filesystem;
        $this->file = $file;
    }

    /**
     * @return Filesystem\Directory\ReadInterface
     */
    private function getPubFolder()
    {
        if (null === $this->pub) {
            $this->pub = $this->filesystem->getDirectoryRead(DirectoryList::PUB);
        }
        return $this->pub;
    }

    /**
     * @param array $files
     * @param string $mode
     */
    public function convertFiles(array $files, int $mode)
    {
        if (!$files) {
            return;
        }

        $files = (is_string($files)) ? [$files] : $files;

        $pubPath = $this->getPubFolder()->getAbsolutePath();
        $imagesExt = ['jpg', 'jpeg', 'png', 'gif'];

        foreach ($files as $file) {

            if ($this->getPubFolder()->isFile($file)) {
                $pathInfo = $this->file->getPathInfo($file);
                if (!isset($pathInfo['extension'])) {
                    continue;
                }
                $ext = strtolower($pathInfo['extension']);

                if (in_array($ext, $imagesExt)) {
                    $image =  $pubPath . $file;
                    $dir = $pubPath . $pathInfo['dirname'];

                    if (isset($this->skipDirs[$dir])) {
                        continue;
                    }

                    $this->createWebPImage->execute($image, $mode);
                }
            }
        }
    }

    /**
     * @param string $path
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFilesFromFolder(string $path = '')
    {
        $clientPath = $path;
        $path = $this->getPath($path);

        if (!$path) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File(s) by path does not exist!  %1', $this->getPubFolder()->getAbsolutePath() . $clientPath)
            );
        }

        //return $this->getPubFolder()->readRecursively($path);
        return $this->readRecursively($path);
    }

    /**
     * @param null $path
     * @return array
     */
    private function readRecursively($path = null)
    {
        //$this->getPubFolder()->validatePath($path);
        $pubFolder = $this->getPubFolder();
        $paths = [];
        $flags = \FilesystemIterator::SKIP_DOTS |
            \FilesystemIterator::UNIX_PATHS |
            \RecursiveDirectoryIterator::FOLLOW_SYMLINKS;

        try {
            $dirItr = new \RecursiveDirectoryIterator($path, $flags);
            $filterItr = new \Magefan\WebP\Model\ImagesRecursiveFilterIterator($dirItr);
            $iterator = new \RecursiveIteratorIterator(
                $filterItr,
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            /** @var \FilesystemIterator $file */
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $paths[] = $file->getPathname();
                }
            }
        } catch (\Exception $e) {
            throw new FileSystemException(new Phrase($e->getMessage()), $e);
        }

        /** @var \FilesystemIterator $file */
        $result = [];
        foreach ($paths as $file) {
            $result[] = $pubFolder->getRelativePath($file);
        }
        sort($result);
        return $result;
    }

    /**
     * @param string $path
     * @return bool|string
     */
    public function getPath(string $path)
    {
        $path = trim($path, '/');

        if ($path == '') {
            return $this->getPubFolder()->getAbsolutePath();
        }

        return ($this->getPubFolder()->isExist($path)) ? $this->getPubFolder()->getAbsolutePath($path) : false;
    }
}
