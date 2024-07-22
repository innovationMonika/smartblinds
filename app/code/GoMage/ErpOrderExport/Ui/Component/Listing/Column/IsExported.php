<?php declare(strict_types=1);

namespace GoMage\ErpOrderExport\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class IsExported extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        foreach ($dataSource['data']['items'] as &$item) {
            $id = $item['smartblinds_registration_id'] ?? 0;
            $isExported = $id ? 1 : 0;
            $item[$this->getName()] = $isExported;
        }
        return $dataSource;
    }
}
