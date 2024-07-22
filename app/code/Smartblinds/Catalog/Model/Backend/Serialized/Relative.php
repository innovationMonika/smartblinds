<?php declare(strict_types=1);

namespace Smartblinds\Catalog\Model\Backend\Serialized;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Smartblinds\Catalog\Model\ResourceModel\RelativeData\Updater\UpdaterInterface;

class Relative extends ArraySerialized
{
    private UpdaterInterface $updater;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        UpdaterInterface $updater,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null
    ) {
        $this->updater = $updater;
        parent::__construct(
            $context, $registry, $config, $cacheTypeList,
            $resource, $resourceCollection, $data, $serializer
        );
    }

    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->updater->update();
        }
        return parent::afterSave();
    }
}
