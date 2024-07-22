<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\MultiFees\Setup\Patch\Data;

use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use MageWorx\MultiFees\Api\Data\FeeInterface;

class UpdateConditionsSerialized implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldDataConverter;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        AggregatedFieldDataConverter $aggregatedFieldDataConverter
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->aggregatedFieldDataConverter = $aggregatedFieldDataConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $feesTableName                  = $this->moduleDataSetup->getTable('mageworx_multifees_fee');
        $columnNameConditionsSerialized = 'conditions_serialized';
        $feeTypeColumnName              = 'type';
        $shippingFeeType                = FeeInterface::SHIPPING_TYPE;
        $paymentFeeType                 = FeeInterface::PAYMENT_TYPE;

        // Update Condition\Combine in the shipping fee
        $this->moduleDataSetup->getConnection()->update(
            $feesTableName,
            [
                $columnNameConditionsSerialized => new \Zend_Db_Expr(
                    sprintf(
                        "REPLACE(%s, '%s', '%s')",
                        $columnNameConditionsSerialized,
                        'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\Combine',
                        'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\ShippingFee\\\\\\\\Combine'
                    )
                )
            ],
            sprintf(
                '`%s` = %d',
                $feeTypeColumnName,
                $shippingFeeType
            )
        );

        // Update Condition\Address in the shipping fee
        $this->moduleDataSetup->getConnection()->update(
            $feesTableName,
            [
                $columnNameConditionsSerialized => new \Zend_Db_Expr(
                    sprintf(
                        "REPLACE(%s, '%s', '%s')",
                        $columnNameConditionsSerialized,
                        'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\Address',
                        'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\ShippingFee\\\\\\\\Address'
                    )
                )
            ],
            sprintf(
                '`%s` = %d',
                $feeTypeColumnName,
                $shippingFeeType
            )
        );

        // Update Condition\Combine in the payment fee
        $this->moduleDataSetup->getConnection()->update(
            $feesTableName,
            [
                $columnNameConditionsSerialized => new \Zend_Db_Expr(
                    sprintf(
                        "REPLACE(%s, '%s', '%s')",
                        $columnNameConditionsSerialized,
                        'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\Combine',
                        'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\PaymentFee\\\\\\\\Combine'
                    )
                )
            ],
            sprintf(
                '`%s` = %d',
                $feeTypeColumnName,
                $paymentFeeType
            )
        );

        // Update Condition\Address in the payment fee
        $this->moduleDataSetup->getConnection()->update(
            $feesTableName,
            [
                $columnNameConditionsSerialized => new \Zend_Db_Expr(
                    sprintf(
                        "REPLACE(%s, '%s', '%s')",
                        $columnNameConditionsSerialized,
                        'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\Address',
                        'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\PaymentFee\\\\\\\\Address'
                    )
                )
            ],
            sprintf(
                '`%s` = %d',
                $feeTypeColumnName,
                $paymentFeeType
            )
        );

    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            \MageWorx\MultiFees\Setup\Patch\Data\ConvertFeeSerializedDataToJson::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.4';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
