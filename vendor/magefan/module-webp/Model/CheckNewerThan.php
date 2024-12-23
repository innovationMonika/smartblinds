<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Model;

use Magento\Framework\Filesystem\Driver\File;

class CheckNewerThan
{
    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @param File $fileDriver
     */
    public function __construct(
        File $fileDriver
    ) {
        $this->fileDriver = $fileDriver;
    }

    /**
     * Return true if image1 newer then image2
     * @param string $image
     * @param string $webpImage
     * @return bool
     */
    public function execute(string $image1, string $image2): bool
    {
        $image1ModificationTime = $this->fileDriver->stat($image1)['mtime'];

        if ($image1ModificationTime === 0) {
            return false;
        }

        $image2ModificationTime = $this->fileDriver->stat($image2)['mtime'];

        return (bool) ($image1ModificationTime > $image2ModificationTime);
    }
}
