<?php declare(strict_types=1);

namespace Smartblinds\ImageImport\Model\Csv\Preparer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\File as Io;
use Magento\Framework\UrlInterface;

class Swatches implements PreparerInterface
{
    private DirectoryList $directoryList;
    private File $file;
    private CollectionFactory $collectionFactory;
    private Csv $csv;
    private Io $io;
    private UrlInterface $url;

    private array $failedImages;

    public function __construct(
        DirectoryList $directoryList,
        File $file,
        CollectionFactory $collectionFactory,
        Csv $csv,
        Io $io,
        UrlInterface $url
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->collectionFactory = $collectionFactory;
        $this->csv = $csv;
        $this->io = $io;
        $this->url = $url;
    }

    public function prepare()
    {
        $this->failedImages = [];
        $imageConfigs = $this->getImageConfigs();
        $mappedImageConfigs = $this->getMappedImageConfigs($imageConfigs);
        $csvData = $this->prepareCsvData($mappedImageConfigs);
        $this->saveCsv($csvData);
    }

    public function getCsvUrl(): string
    {
        return $this->url->getDirectUrl('media/smartblinds/swatches.csv');
    }

    public function getFailedImages(): array
    {
        return $this->failedImages;
    }

    private function saveCsv(array $csvData)
    {
        $mediaDir = $this->directoryList->getPath(DirectoryList::MEDIA);
        $this->io->checkAndCreateFolder("$mediaDir/smartblinds");
        $pathToSave = "$mediaDir/smartblinds/swatches.csv";
        $this->csv->appendData($pathToSave, $csvData, 'w+');
    }

    private function prepareCsvData(array $mappedImageConfigs): array
    {
        $csvData = [
            [
                'sku',
                'swatch_image',
                'swatch_image_label'
            ]
        ];

        foreach ($mappedImageConfigs as $sku => $imageConfig) {
            $csvData[] = [
                'sku' => $sku,
                'swatch_image' => $imageConfig['basename'],
                'swatch_image_label' => $imageConfig['filename']
            ];
        }

        return $csvData;
    }

    private function getImageConfigs(): array
    {
        $varDir = $this->directoryList->getPath(DirectoryList::VAR_DIR);
        $paths = $this->file->readDirectory("$varDir/import/images/swatches/");

        $imageConfigs = [];
        foreach ($paths as $path) {
            $pathParts = pathinfo($path);
            $basename = $pathParts['basename'];
            $filename = $pathParts['filename'];
            $smartblindsSku = $filename;

            $config = [
                'basename' => $basename,
                'filename' => $filename,
                'smartblinds_sku' => $smartblindsSku
            ];

            $imageConfigs[] = $config;
        }

        return $imageConfigs;
    }

    private function getMappedImageConfigs(array $imageConfigs): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter('type_id', ['eq' => Type::TYPE_SIMPLE]);
        $collection->addAttributeToSelect('smartblinds_sku');

        $mapped = [];
        foreach ($imageConfigs as $imageConfig) {
            foreach ($collection as $product) {
                /** @var Product $product */
                $productSmartblindsSku = $product->getData('smartblinds_sku');
                if ($productSmartblindsSku == $imageConfig['smartblinds_sku']) {
                    $mapped[$product->getSku()] = $imageConfig;
                }
            }
        }
        return $mapped;
    }
}
