<?php declare(strict_types=1);

namespace Smartblinds\Elasticsearch\Plugin\Model\Adapter\BatchDataMapper\ProductDataMapper;

class ConvertSmartblindsPrice
{
    public function afterMap(
        \Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper $subject,
        array $result
    ) {
        foreach ($result as &$row) {
            if (isset($row['smartblinds_price'])) {
                $row['smartblinds_price'] = (float) $row['smartblinds_price'];
            }
        }
        return $result;
    }
}
