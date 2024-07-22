<?php /** @noinspection ALL */

/**
 * Copyright © 2018 Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\PageHierarchy\Model;

use Mageside\PageHierarchy\Helper\PageHierarchy;
use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\StoreManagerInterface;

class OptionsSelect implements ArrayInterface
{
    /**
     * All Store Views value
     */
    const ALL_STORE = '0';

    /**
     * @var PageHierarchy
     */
    protected $helper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var string
     */
    private $dash;

    /**
     * @var array
     */
    protected $_options;


    /**
     * OptionsParentSelect constructor.
     * @param PageHierarchy $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PageHierarchy $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->dash = html_entity_decode('&#8212;', ENT_COMPAT, 'UTF-8');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptionArray();
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        if (is_null($this->_options)) {
            $noParent = __("-- Please Select --");
            $optionsArray = [
                0 => [
                    'value' => 0,
                    'label' => $noParent
                ]
            ];

            $storesMap = $this->storeManager->getStores();
            $tree = $this->helper->getArrayHierarchy(\Mageside\PageHierarchy\Helper\PageHierarchy::ADMIN);
            foreach ($tree as $items) {
                $this->parseArrayHierarchy($optionsArray, $items, $storesMap, $this->dash);
            }

            $this->_options = $optionsArray;
        }

        return $this->_options;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $this->getOptionArray();
    }

    /**
     * @param $optionId
     * @return mixed|null
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    protected function parseArrayHierarchy(&$optionsArray, $items, $storesMap, $dashToken = '-', $dashes = '')
    {
        foreach ($items as $item) {
            $pageId = $item['page_id'];
            $storeIds = $item['store_id'];
            $storeNames = [];
            foreach ($storeIds as $storeId) {
                if ($storeId == self::ALL_STORE && count($storeIds) >= 1) {
                    $storeNames[] = __('All Store');
                    continue;
                } elseif (isset($storesMap[$storeId])) {
                    $storeNames[] = $storesMap[$storeId]->getName();
                }
            }

            $optionsArray[] = [
                'value' => $pageId,
                'label' => $dashes . " " . $item['title'] . " (id : $pageId - " . implode(',', $storeNames) . ")"
            ];
            if (isset($item['children'])) {
                $dashes .= $dashToken;
                $this->parseArrayHierarchy($optionsArray, $item['children'], $storesMap, $dashToken, $dashes);
            }
        }
    }
}
