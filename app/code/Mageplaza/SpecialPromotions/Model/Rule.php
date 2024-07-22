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

namespace Mageplaza\SpecialPromotions\Model;

use Magento\Rule\Model\Action\Collection;

/**
 * Class Rule
 * @package Mageplaza\SpecialPromotions\Model
 * @method hasMpProductXActionsSerialized()
 * @method getMpProductXActionsSerialized()
 * @method unsMpProductXActionsSerialized()
 * @method hasMpProductYActionsSerialized()
 * @method getMpProductYActionsSerialized()
 * @method unsMpProductYActionsSerialized()
 */
class Rule extends \Magento\SalesRule\Model\Rule
{
    /**
     * Store rule actions model
     *
     * @var Collection
     */
    protected $productXActions;

    /**
     * Store rule actions model
     *
     * @var Collection
     */
    protected $productYActions;

    /**
     * Set rule actions model
     *
     * @param Collection $productXActions
     *
     * @return $this
     */
    public function setProductXActions($productXActions)
    {
        $this->productXActions = $productXActions;

        return $this;
    }

    /**
     * Set rule actions model
     *
     * @param Collection $productYActions
     *
     * @return $this
     */
    public function setProductYActions($productYActions)
    {
        $this->productYActions = $productYActions;

        return $this;
    }

    /**
     * Retrieve rule actions model
     *
     * @return Collection
     */
    public function getProductXActions()
    {
        if (!$this->productXActions) {
            $this->_resetProductXActions();
        }

        // Load rule actions if it is applicable
        if ($this->hasMpProductXActionsSerialized()) {
            $productXActions = $this->getMpProductXActionsSerialized();
            if (!empty($productXActions)) {
                $productXActions = $this->serializer->unserialize($productXActions);
                if (is_array($productXActions) && !empty($productXActions)) {
                    $this->productXActions->loadArray($productXActions);
                }
            }
            $this->unsMpProductXActionsSerialized();
        }

        return $this->productXActions;
    }

    /**
     * Retrieve rule actions model
     *
     * @return Collection
     */
    public function getProductYActions()
    {
        if (!$this->productYActions) {
            $this->_resetProductYActions();
        }

        // Load rule actions if it is applicable
        if ($this->hasMpProductYActionsSerialized()) {
            $productYActions = $this->getMpProductYActionsSerialized();
            if (!empty($productYActions)) {
                $productYActions = $this->serializer->unserialize($productYActions);
                if (is_array($productYActions) && !empty($productYActions)) {
                    $this->productYActions->loadArray($productYActions);
                }
            }
            $this->unsMpProductYActionsSerialized();
        }

        return $this->productYActions;
    }

    /**
     * Reset rule actions
     *
     * @param null|Collection $productXActions
     *
     * @return $this
     */
    protected function _resetProductXActions($productXActions = null)
    {
        if ($productXActions === null) {
            $productXActions = $this->getActionsInstance();
        }
        $productXActions->setRule($this)->setId('1')->setPrefix('mp_product_x_actions');
        $this->setProductXActions($productXActions);

        return $this;
    }

    /**
     * Reset rule actions
     *
     * @param null|Collection $productYActions
     *
     * @return $this
     */
    protected function _resetProductYActions($productYActions = null)
    {
        if ($productYActions === null) {
            $productYActions = $this->getActionsInstance();
        }
        $productYActions->setRule($this)->setId('1')->setPrefix('mp_product_y_actions');
        $this->setProductYActions($productYActions);

        return $this;
    }
}
