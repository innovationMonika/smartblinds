<?php

namespace Smartblinds\System\Ui\Component\Listing\Columns\System;

use Magento\Ui\Component\Listing\Columns\Column;

class FabricSize extends Column
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
                if(!empty($item['fabric_size']) && !empty($item['system_category']) && $item['system_category'] !== 'honeycomb_blinds'){
                    $item['fabric_size'] = null;
                }
            }
        }

        return $dataSource;
    }
}
