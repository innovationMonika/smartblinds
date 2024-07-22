<?php

namespace Smartblinds\System\Ui\Component\Listing\Columns\System;

use Magento\Ui\Component\Listing\Columns\Column;

class SystemSize extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!empty($item['system_size']) && !empty($item['system_category']) && in_array($item['system_category'], ['venetian_blinds', 'honeycomb_blinds'])){
                    $item['system_size'] = null;
                }
            }
        }

        return $dataSource;
    }
}
