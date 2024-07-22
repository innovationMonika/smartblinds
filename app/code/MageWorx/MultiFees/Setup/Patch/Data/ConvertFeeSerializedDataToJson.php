<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\MultiFees\Setup\Patch\Data;

use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use MageWorx\MultiFees\Api\Data\FeeInterface;

class ConvertFeeSerializedDataToJson implements DataPatchInterface, PatchVersionInterface
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
        $this->aggregatedFieldDataConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $this->moduleDataSetup->getTable('mageworx_multifees_fee'),
                    FeeInterface::FEE_ID,
                    'conditions_serialized'
                ),
            ],
            $this->moduleDataSetup->getConnection()
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
        return '2.0.2';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
