<?php declare(strict_types=1);

namespace Smartblinds\Bundling\Plugin\View\Page\Config;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Page\Config;

class ProcessPayloadAsset
{
    private File $file;
    private Filesystem $filesystem;
    private Repository $assetRepository;

    public function __construct(
        File $file,
        Filesystem $filesystem,
        Repository $assetRepository
    ) {
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->assetRepository = $assetRepository;
    }

    public function aroundAddPageAsset(
        Config $subject,
        callable $proceed,
        $file,
        array $properties = [],
        $name = null
    ) {
        if (strpos($file, 'bundles/') !== 0) {
            return $proceed($file, $properties, $name);
        }
        $staticPath = $this->filesystem
            ->getDirectoryRead(DirectoryList::STATIC_VIEW)
            ->getAbsolutePath();
        $asset = $this->assetRepository->createAsset($file);
        $assetPath = $asset->getRelativeSourceFilePath();
        $fullPath = rtrim($staticPath, '/')
            . '/' . ltrim($assetPath, '/');
        return $this->file->isExists($fullPath) ?
            $proceed($file, $properties, $name) : $subject;
    }
}
