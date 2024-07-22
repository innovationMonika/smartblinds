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
use Smartblinds\ImageImport\Model\Config;
use Smartblinds\System\Model\ResourceModel\System\CollectionFactory as SystemCollectionFactory;

class Images implements PreparerInterface
{
    private DirectoryList $directoryList;
    private File $file;
    private Config $config;
    private CollectionFactory $collectionFactory;
    private Csv $csv;
    private Io $io;
    private UrlInterface $url;
    private SystemCollectionFactory $systemCollectionFactory;

    private array $failedImages;

    public function __construct(
        DirectoryList $directoryList,
        File $file,
        Config $config,
        CollectionFactory $collectionFactory,
        Csv $csv,
        Io $io,
        UrlInterface $url,
        SystemCollectionFactory $systemCollectionFactory
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->csv = $csv;
        $this->io = $io;
        $this->url = $url;
        $this->systemCollectionFactory = $systemCollectionFactory;
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
        return $this->url->getDirectUrl('media/smartblinds/images.csv');
    }

    public function getFailedImages(): array
    {
        return $this->failedImages;
    }

    private function saveCsv(array $csvData)
    {
        $mediaDir = $this->directoryList->getPath(DirectoryList::MEDIA);
        $this->io->checkAndCreateFolder("$mediaDir/smartblinds");
        $pathToSave = "$mediaDir/smartblinds/images.csv";
        $this->csv->appendData($pathToSave, $csvData, 'w+');
    }

    private function prepareCsvData(array $mappedImageConfigs): array
    {
        $csvData = [
            [
                'sku',
                'base_image',
                'base_image_label',
                'small_image',
                'small_image_label',
                'thumbnail_image',
                'thumbnail_image_label',
                'additional_images'
            ]
        ];

        foreach ($mappedImageConfigs as $sku => $imageConfigs) {
            $sortedImageConfigs = $this->sortImageConfigs($imageConfigs);
            $firstImage = reset($sortedImageConfigs);
            unset($sortedImageConfigs[0]);
            $csvData[] = [
                'sku' => $sku,
                'base_image' => $firstImage['basename'],
                'base_image_label' => $firstImage['filename'],
                'small_image' => $firstImage['basename'],
                'small_image_label' => $firstImage['filename'],
                'thumbnail_image' => $firstImage['basename'],
                'thumbnail_image_label' => $firstImage['filename'],
                'additional_images' => implode(',', array_map(function ($imageConfig) {
                    return $imageConfig['basename'];
                }, $sortedImageConfigs)),
            ];
        }

        return $csvData;
    }

    private function sortImageConfigs(array $imageConfigs): array
    {
        $pictures = [];
        foreach ($imageConfigs as $imageConfig) {
            $position = $this->config->getImagePosition($imageConfig['view']);
            $pictures[$position] = $imageConfig;
        }
        ksort($pictures);
        return array_values($pictures);
    }

    private function getMappedImageConfigs(array $imageConfigs): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter('type_id', ['eq' => Type::TYPE_SIMPLE]);
        $collection->addAttributeToSelect($this->getMapAttributes());

        $mapped = [];
        foreach ($imageConfigs as $imageConfig) {
            $products = $collection->getItemsByColumnValue('smartblinds_sku', $imageConfig['smartblinds_sku']);
            foreach ($products as $product) {
                /** @var Product $product */
                $checksPassed = true;
                foreach ($this->getMapAttributes() as $attributeCode) {
                    if (empty($imageConfig[$attributeCode]) && empty($product->getData($attributeCode))) {
                        continue;
                    }
                    if ($imageConfig[$attributeCode] != $product->getData($attributeCode)) {
                        $checksPassed = false;
                    }
                }
                if ($checksPassed) {
                    $mapped[$product->getSku()][] = $imageConfig;
                }
            }
        }
        return $mapped;
    }

    private function getImageConfigs(): array
    {
        $varDir = $this->directoryList->getPath(DirectoryList::VAR_DIR);
        $paths = $this->file->readDirectory("$varDir/import/images/");

        $imageConfigs = [];
        foreach ($paths as $path) {
            $pathParts = pathinfo($path);
            $basename = $pathParts['basename'];
            $filename = $pathParts['filename'];
            $parts = explode('_', $filename);
            $systemCode = $parts[0] ?? null;
            if (sizeof($parts) !== 4 || !$systemCode) {
                $this->failedImages[] = $basename . ' - wrong parts count (' . sizeof($parts) . ')';
                continue;
            }
            if (!$systemCode) {
                $this->failedImages[] = $basename . ' - no system code';
                continue;
            }
            $systemColor = $parts[1];
            $systemColor = $systemColor === 'SKIP' ? null : $systemColor;
            $smartblindsSku = $parts[2];
            $view = $parts[3];

            if (!in_array($view, $this->config->getImageCodes())) {
                $this->failedImages[] = $basename . " - wrong image code $view";
                continue;
            }

            $attributes = ['system_color' => $systemColor];

            foreach ($attributes as $attribute => $value) {
                if (!$value) {
                    $attributes[$attribute] = null;
                    continue;
                }
                $optionId = $this->config->getSystemAttributeOptionId($attribute, $value);
                if (!$optionId) {
                    $this->failedImages[] = $basename . " - option id not found for attribute $attribute and value $value";
                    continue 2;
                }
                $attributes[$attribute] = $optionId;
            }

            $config = array_merge($attributes, [
                'basename'        => $basename,
                'filename'        => $filename,
                'system_code'     => $systemCode,
                'smartblinds_sku' => $smartblindsSku,
                'view'            => $view
            ]);

            $imageConfigs[] = $config;
        }

        $systemCodes = array_column($imageConfigs, 'system_code');
        $systemCollection = $this->systemCollectionFactory->create();
        $systemCollection->addFieldToFilter('code', ['in', $systemCodes]);
        foreach ($imageConfigs as $index => $imageConfig) {
            $systemCode = $imageConfig['system_code'];
            $system = $systemCollection->getItemByColumnValue('code', $systemCode);
            if (!$system) {
                $this->failedImages[] = $imageConfig['basename'] . " - system not found with code $systemCode";
                unset($imageConfigs[$index]);
                continue;
            }
            $imageConfigs[$index]['system_type'] = $system->getData('system_type');
            $imageConfigs[$index]['system_size'] = $system->getData('system_size');
            $imageConfigs[$index]['fabric_size'] = $system->getData('fabric_size');
            $imageConfigs[$index]['control_type'] = $system->getData('control_type');
        }

        return $imageConfigs;
    }

    private function getMapAttributes(): array
    {
        return [
            'system_type',
            'system_size',
            'system_color',
            'fabric_size',
            'control_type',
            'smartblinds_sku'
        ];
    }
}
