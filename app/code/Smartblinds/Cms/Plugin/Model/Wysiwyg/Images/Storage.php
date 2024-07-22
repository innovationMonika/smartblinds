<?php declare(strict_types=1);

namespace Smartblinds\Cms\Plugin\Model\Wysiwyg\Images;

use Magento\Framework\App\Filesystem\DirectoryList;

class Storage
{
    /**
     * @var string
     */
    const IMAGE_EXTENSION_WEBP = 'webp';

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directory;

    /**
     * @var \Magento\Cms\Helper\Wysiwyg\Images|null
     */
    protected $_cmsWysiwygImages = null;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages
    ) {
        $this->_cmsWysiwygImages = $cmsWysiwygImages;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage $subject
     * @param callable $proceed
     * @param $source
     * @param $keepRatio
     * @return false
     */
    public function aroundResizeFile(
        \Magento\Cms\Model\Wysiwyg\Images\Storage $subject,
        callable $proceed,
        $source,
        $keepRatio = true
    ) {
        $fileInfo = pathinfo($source);
        if (isset($fileInfo['extension']) && strtolower($fileInfo['extension']) === self::IMAGE_EXTENSION_WEBP) {
            return false;
        }
        return $proceed($source, $keepRatio);
    }

    /**
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage $subject
     * @param callable $proceed
     * @param $filePath
     * @param $checkFile
     * @return string
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function aroundGetThumbnailUrl(
        \Magento\Cms\Model\Wysiwyg\Images\Storage $subject,
        callable $proceed,
        $filePath,
        $checkFile = false
    ) {
        $fileInfo = pathinfo($filePath);
        if (isset($fileInfo['extension']) && strtolower($fileInfo['extension']) === self::IMAGE_EXTENSION_WEBP) {
            $thumbRelativePath = ltrim($this->_directory->getRelativePath($filePath), '/\\');
            $baseUrl = rtrim($this->_cmsWysiwygImages->getBaseUrl(), '/');
            $randomIndex = '?rand=' . time();
            return str_replace('\\', '/', $baseUrl . '/' . $thumbRelativePath) . $randomIndex;
        }
        return $proceed($filePath, $checkFile);
    }
}
