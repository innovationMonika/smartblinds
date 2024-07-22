<?php declare(strict_types=1);

namespace Smartblinds\System\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class System extends AbstractDb
{
    const TABLE_NAME = 'smartblinds_system';

    protected function _construct()
    {
        $this->_init(static::TABLE_NAME, 'id');
    }
}
