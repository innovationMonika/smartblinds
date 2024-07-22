<?php

namespace GoMage\Addons\Plugin;

class CategoryDataProvider
{
    /**
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param $result
     * @return array
     */
    public function afterGetData(\Magento\Catalog\Model\Category\DataProvider $subject, $result)
    {
        $key = key($result);

        if(!empty($result[$key]['func_systeem'])){
            $result[$key]['func_systeem'] = json_decode($result[$key]['func_systeem'], true);
        }
        if(!empty($result[$key]['func_garantie'])){
            $result[$key]['func_garantie'] = json_decode($result[$key]['func_garantie'], true);
        }
        if(!empty($result[$key]['func_stof'])){
            $result[$key]['func_stof'] = json_decode($result[$key]['func_stof'], true);
        }

        return $result;
    }
}
