<?php declare(strict_types=1);

namespace Smartblinds\System\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\AbstractModel;
use Smartblinds\System\Model\ResourceModel\System as SystemResource;

class System extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(SystemResource::class);
    }

    public function getStoreBasePrice()
    {
        $priceConfig = $this->getConfig()->getSystemPrice($this, 'base_price');
        $price = $priceConfig ?: $this->getData('base_price');
        return $price;
    }

    public function getStoreMeterPrice()
    {
        $priceConfig = $this->getConfig()->getSystemPrice($this, 'meter_price');
        $price = $priceConfig ?: $this->getData('meter_price');
        return $price;
    }

    public function getStoreMaxWidthPrice()
    {
        $maxWidthConfig = $this->getConfig()->getSystemDimensionValue($this, 'max_width');
        $maxWidth = $maxWidthConfig ?: $this->getData('max_width');
        return $maxWidth;
    }

    public function getStoreMaxHeightPrice()
    {
        $maxHeightConfig = $this->getConfig()->getSystemDimensionValue($this, 'max_height');
        $maxHeight = $maxHeightConfig ?: $this->getData('max_height');
        return $maxHeight;
    }

    private function getConfig()
    {
        return ObjectManager::getInstance()->get(Config::class);
    }
}
