<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\MultiFees\Setup\Patch\Data;

use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use MageWorx\MultiFees\Setup\SerializedToJsonDataConverter;

class ConvertSalesFeeSerializedDataToJson implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    public function __construct(
        FieldDataConverterFactory $fieldDataConverterFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $fields = ['mageworx_fee_details', 'mageworx_product_fee_details'];
        $tables = ['sales_order', 'sales_invoice', 'sales_creditmemo'];

        foreach ($fields as $field) {
            foreach ($tables as $table) {
                $this->convertSerializedDataToJsonInTables($table, $field);
            }

            $this->convertSerializedDataToJsonInTables(
                'quote_address',
                $field,
                'address_id'
            );
        }
    }

    /**
     * @param string $tableName
     * @param string $field
     * @param string $identifier
     */
    protected function convertSerializedDataToJsonInTables(
        $tableName,
        $field,
        $identifier = 'entity_id'
    ) {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(
            SerializedToJsonDataConverter::class
        );

        $fieldDataConverter->convert(
            $this->moduleDataSetup->getConnection(),
            $this->moduleDataSetup->getTable($tableName),
            $identifier,
            $field
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.10';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
