<?php declare(strict_types=1);

namespace Smartblinds\CatalogSearch\Plugin\Catalog\Model\Layer\CollectionFilter;

use Smartblinds\CatalogSearch\Model\Config;
use Magento\Catalog\Model\Layer\CollectionFilterInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class AddTypeIdFilter
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function afterFilter(CollectionFilterInterface $subject, $result, Collection $collection)
    {
        $collection->addFieldToFilter('type_id', $this->config->getProductTypesToExcludeFromLayer());
        return $result;
    }
}
