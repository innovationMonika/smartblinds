<?php declare(strict_types=1);

namespace Smartblinds\System\Model\ResourceModel\System;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Smartblinds\System\Model\ResourceModel\System as SystemResource;
use Smartblinds\System\Model\System;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(System::class, SystemResource::class);
    }
}
