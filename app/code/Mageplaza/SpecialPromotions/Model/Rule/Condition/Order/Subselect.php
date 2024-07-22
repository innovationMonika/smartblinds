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

namespace Mageplaza\SpecialPromotions\Model\Rule\Condition\Order;

use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory as ReportCollection;
use Magento\Rule\Model\Condition\Context;
use Magento\Sales\Model\Order;
use Zend_Db_Expr;

/**
 * Subselect conditions for order.
 *
 * Class Subselect
 * @package Mageplaza\SpecialPromotions\Model\Rule\Condition\Order
 */
class Subselect extends Combine
{
    /**
     * @var ReportCollection
     */
    private $reportCollection;

    /**
     * Subselect constructor.
     *
     * @param Context $context
     * @param ReportCollection $reportCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        ReportCollection $reportCollection,
        array $data = []
    ) {
        $this->reportCollection = $reportCollection;

        parent::__construct($context, $data);

        $this->setType(__CLASS__)->setValue(null);
    }

    /**
     * Load array
     *
     * @param array $arr
     * @param string $key
     *
     * @return $this
     */
    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);

        return $this;
    }

    /**
     * Return as xml
     *
     * @param string $containerKey
     * @param string $itemKey
     *
     * @return string
     */
    public function asXml($containerKey = 'conditions', $itemKey = 'condition')
    {
        $xml = '<attribute>' .
            $this->getAttribute() .
            '</attribute>' .
            '<operator>' .
            $this->getOperator() .
            '</operator>' .
            parent::asXml(
                $containerKey,
                $itemKey
            );

        return $xml;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption([
            'qty'                 => __('total quantity'),
            'base_grand_total'    => __('total amount'),
            'base_total_paid'     => __('total paid amount'),
            'base_total_refunded' => __('total refunded amount'),
            'average'             => __('average amount'),
            'total_order_revenue' => __('total order revenue')
        ]);

        return $this;
    }

    /**
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        return $this;
    }

    /**
     * Load operator options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                '=='  => __('is'),
                '!='  => __('is not'),
                '>='  => __('equals or greater than'),
                '<='  => __('equals or less than'),
                '>'   => __('greater than'),
                '<'   => __('less than'),
                '()'  => __('is one of'),
                '!()' => __('is not one of'),
            ]
        );

        return $this;
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Return as html
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml()
            . __(
                'If %1 %2 %3 for a subselection of orders matching %4 of these conditions (leave blank for all orders):',
                $this->getAttributeElement()->getHtml(),
                $this->getOperatorElement()->getHtml(),
                $this->getValueElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    /**
     * Validate
     *
     * @param AbstractModel $model
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate(AbstractModel $model)
    {
        return $this->validateAttribute($this->getDataToValidate($model));
    }

    /**
     * @param $address
     *
     * @return float
     */
    protected function getDataToValidate($address)
    {
        $customerId = $address->getCustomerId();
        if (!$customerId) {
            return 0;
        }

        $reportCollection = $this->reportCollection->create();
        $reportCollection->setMainTable('sales_order');
        $reportCollection->addFieldToFilter('customer_id', $customerId);

        foreach ($this->getConditions() as $cond) {
            foreach ($reportCollection as $order) {
                if (!$cond->validate($order)) {
                    $reportCollection->removeItemByKey($order->getId());
                }
            }
        }

        $select = $reportCollection->getSelect();
        $reportCollection->removeAllFieldsFromSelect();
        $reportConnection = $reportCollection->getConnection();

        $expressionTransferObject = new DataObject(
            [
                'expression' => '%s - %s - %s - (%s - %s - %s)',
                'arguments'  => [
                    $reportConnection->getIfNullSql('main_table.base_total_invoiced', 0),
                    $reportConnection->getIfNullSql('main_table.base_tax_invoiced', 0),
                    $reportConnection->getIfNullSql('main_table.base_shipping_invoiced', 0),
                    $reportConnection->getIfNullSql('main_table.base_total_refunded', 0),
                    $reportConnection->getIfNullSql('main_table.base_tax_refunded', 0),
                    $reportConnection->getIfNullSql('main_table.base_shipping_refunded', 0),
                ],
            ]
        );
        $revenueExp               = vsprintf(
            $expressionTransferObject->getExpression(),
            $expressionTransferObject->getArguments()
        );

        switch ($this->getAttribute()) {
            case 'qty':
                $result = $reportCollection->getSize();
                break;
            case 'base_grand_total':
                $reportCollection->getSelect()->columns(
                    [
                        'special_base_grand_total' => 'SUM(main_table.base_grand_total)'
                    ]
                );
                $result = $reportCollection->getFirstItem()->getSpecialBaseGrandTotal();
                break;
            case 'base_total_paid':
                $reportCollection->getSelect()->columns(
                    [
                        'special_base_total_paid' => 'SUM(main_table.base_total_paid)'
                    ]
                );
                $result = $reportCollection->getFirstItem()->getSpecialBaseTotalPaid();
                break;
            case 'base_total_refunded':
                $reportCollection->getSelect()->columns(
                    [
                        'special_base_total_refunded' => 'SUM(main_table.base_total_refunded)'
                    ]
                );
                $result = $reportCollection->getFirstItem()->getSpecialBaseTotalRefuned();
                break;
            case 'total_order_revenue':
                $reportCollection->getSelect()->columns(
                    [
                        'special_total_order_revenue' => new Zend_Db_Expr(sprintf('SUM(%s)', $revenueExp))
                    ]
                );
                $select->where('main_table.state NOT IN (?)', [Order::STATE_PENDING_PAYMENT, Order::STATE_NEW]);
                $result = $reportCollection->getFirstItem()->getSpecialTotalOrderRevenue();
                break;
            case 'average':
                $reportCollection->getSelect()->columns(
                    [
                        'special_average' => 'IF(main_table.entity_id IS NULL, 0, SUM(main_table.base_grand_total)/COUNT(main_table.entity_id))'
                    ]
                );
                $result = $reportCollection->getFirstItem()->getSpecialAverage();
                break;
            default:
                $result = 0;
        }

        return $result;
    }
}
