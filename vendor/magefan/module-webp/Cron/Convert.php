<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Cron;

use Magefan\WebP\Model\Filesystem\PubFolder;
use Magefan\WebP\Model\Config\Source\CreationOptions;
use Magefan\WebP\Model\Config;
use Magefan\WebP\Model\ResourceModel\Image\CollectionFactory as ImageQueueCollectionFactory;
use Magefan\WebP\Model\ResourceModel\Image\Collection as ImageCollection;

/**
 * Class GenerateWebPByCron
 */
class Convert
{
    /**
     * @var PubFolder
     */
    private $pubFolder;

    /**
     * @var ImageQueueCollectionFactory
     */
    private $imageQueueCollectionFactory;

    /**
     * @param PubFolder $pubFolder
     * @param Config $config
     * @param ImageQueueCollectionFactory $imageQueueCollectionFactory
     */
    public function __construct(
        PubFolder $pubFolder,
        Config $config,
        ImageQueueCollectionFactory $imageQueueCollectionFactory
    ) {
        $this->pubFolder = $pubFolder;
        $this->config = $config;
        $this->imageQueueCollectionFactory = $imageQueueCollectionFactory;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        if (!in_array(
            $this->config->getGenerationOption(),
            [
                CreationOptions::CRON,
                CreationOptions::PAGE_LOAD_AND_CRON
            ]
        )
        ) {
            return;
        }

        $itemQueueCollection = $this->getImageQueueCollection();

        if (!count($itemQueueCollection)) {
            $this->generateImageQueue($itemQueueCollection);
        } else {
            $this->processImageQueue($itemQueueCollection);
        }
    }

    /**
     * @param ImageCollection $itemQueueCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function generateImageQueue(ImageCollection $itemQueueCollection): void
    {
        /* Reset increment */
        $mainTable = $itemQueueCollection->getMainTable();

        $connection = $itemQueueCollection->getConnection();
        $connection->truncateTable($mainTable);

        $items = $this->pubFolder->getFilesFromFolder();
        $i = 0;
        $data = [];

        foreach ($items as $image) {
            $data[] = [
                'image' => $image
            ];

            $i++;

            if ($i >= 100000) {
                break;
            }
        }

        if ($data) {
            $connection->insertMultiple($mainTable, $data);
        }
    }

    /**
     * @param ImageCollection $itemQueueCollection
     */
    private function processImageQueue(ImageCollection $itemQueueCollection): void
    {
        $startTime = time();

        while (count($itemQueueCollection)) {
            foreach ($itemQueueCollection as $item) {
                $this->pubFolder->convertFiles([$item->getData('image')], CreationOptions::CRON);

                $item->delete();

                if ((time() - $startTime) > (4 * 60 + 50)) {
                    return;
                }
            }

            $itemQueueCollection = $this->getImageQueueCollection();
        }
    }

    /**
     * @return ImageCollection
     */
    private function getImageQueueCollection(): ImageCollection
    {
        return $this->imageQueueCollectionFactory->create()
            ->setPageSize(100);
    }
}
