<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SpecialPromotions
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SpecialPromotions\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\SpecialPromotions\Helper\Data;
use Mageplaza\SpecialPromotions\Model\Config\Source\RuleType;
use Mageplaza\SpecialPromotions\Model\RuleFactory;

/**
 * Class ProductActions
 * @package Mageplaza\SpecialPromotions\Observer
 */
class ProductActions implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * ProductActions constructor.
     *
     * @param RequestInterface $request
     * @param RuleFactory $ruleFactory
     */
    public function __construct(RequestInterface $request, RuleFactory $ruleFactory)
    {
        $this->request = $request;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $dataObject = $observer->getEvent()->getDataObject();
        $data = $this->request->getParams();
        if (isset($data['rule']['mp_product_x_actions'])) {
            $data['mp_product_x_actions'] = $data['rule']['mp_product_x_actions'];
        }
        if (isset($data['rule']['mp_product_y_actions'])) {
            $data['mp_product_y_actions'] = $data['rule']['mp_product_y_actions'];
        }
        $arr = $this->_convertFlatToRecursive($data);
        $specialPromotions = $this->ruleFactory->create();
        if (isset($arr['mp_product_x_actions'])) {
            $specialPromotions->getProductXActions()
                ->setProductXActions([])
                ->loadArray($arr['mp_product_x_actions'][1], 'mp_product_x_actions');
            $dataObject->setMpProductXActionsSerialized(
                Data::jsonEncode($specialPromotions->getProductXActions()->asArray())
            );
        }
        if (isset($arr['mp_product_y_actions'])) {
            $specialPromotions->getProductYActions()
                ->setProductYActions([])
                ->loadArray($arr['mp_product_y_actions'][1], 'mp_product_y_actions');
            $dataObject->setMpProductYActionsSerialized(
                Data::jsonEncode($specialPromotions->getProductYActions()->asArray())
            );
        }
        if (isset($data['simple_action']) && $data['simple_action'] === RuleType::BUY_X_ITEM_GET_Y_ITEM) {
            $dataObject->setItemAction('none');
        }

        return $this;
    }

    /**
     * Set specified data to current rule.
     * Set conditions and actions recursively.
     * Convert dates into \DateTime.
     *
     * @param array $data
     *
     * @return array
     */
    protected function _convertFlatToRecursive(array $data)
    {
        $arr = [];
        foreach ($data as $key => $value) {
            if (($key === 'mp_product_x_actions' || $key === 'mp_product_y_actions') && is_array($value)) {
                foreach ($value as $id => $childData) {
                    $path = explode('--', $id);
                    $node = &$arr;
                    for ($i = 0, $l = count($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = &$node[$key][$path[$i]];
                    }
                    foreach ($childData as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            }
        }

        return $arr;
    }
}
