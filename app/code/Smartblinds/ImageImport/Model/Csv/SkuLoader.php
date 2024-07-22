<?php declare(strict_types=1);

namespace Smartblinds\ImageImport\Model\Csv;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv;

class SkuLoader
{
    private DirectoryList $directoryList;
    private Csv $csv;

    public function __construct(
        DirectoryList $directoryList,
        Csv $csv
    ) {
        $this->directoryList = $directoryList;
        $this->csv = $csv;
    }

    public function loadSkus(string $filename): array
    {
        $mediaDir = $this->directoryList->getPath(DirectoryList::MEDIA);
        $pathToRead = "$mediaDir/smartblinds/$filename.csv";
        try {
            $csvData = $this->csv->getData($pathToRead);
        } catch (\Exception $e) {
            return [];
        }

        if (!$csvData) {
            return [];
        }

        unset($csvData[0]);
        return array_map(function ($row) {
            return $row[0];
        }, $csvData);
    }
}
