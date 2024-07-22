<?php

namespace GoMage\Addons\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;

class Data extends AbstractHelper
{
    const FUNC_SYSTEEM_ROWS = 'func_systeem';
    const FUNC_STOF_ROWS = 'func_stof';
    const FUNC_GARANTIE_ROWS = 'func_garantie';

    /**
     * @param Product|Category $object
     * @param $data
     * @return void
     */
    public function setObjectData($object, $data)
    {
        $funcData = $data[self::FUNC_SYSTEEM_ROWS] ?? [];
        $object->setData(self::FUNC_SYSTEEM_ROWS, json_encode($funcData));

        $funcData = $data[self::FUNC_STOF_ROWS] ?? [];
        $object->setData(self::FUNC_STOF_ROWS, json_encode($funcData));

        $funcData = $data[self::FUNC_GARANTIE_ROWS] ?? [];
        $object->setData(self::FUNC_GARANTIE_ROWS, json_encode($funcData));
    }
}
