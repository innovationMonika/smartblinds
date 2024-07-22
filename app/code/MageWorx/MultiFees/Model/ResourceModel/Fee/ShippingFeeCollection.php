<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\ResourceModel\Fee;

use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Exception\RefactoringException;

class ShippingFeeCollection extends AbstractCollection
{
    const FEE_MODEL_CLASS_NAME          = 'MageWorx\MultiFees\Model\ShippingFee';
    const FEE_RESOURCE_MODEL_CLASS_NAME = 'MageWorx\MultiFees\Model\ResourceModel\ShippingFeeResource';

    /**
     * ShippingFeeCollection constructor.
     *
     * @param \MageWorx\MultiFees\Helper\Data $helperFee
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \MageWorx\MultiFees\Helper\Data $helperFee,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $helperFee,
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $connection,
            $resource
        );
    }

    /**
     * Always filter fee collection by its own type
     *
     * @return $this
     * @throws RefactoringException
     */
    protected function addFeeTypeFilter()
    {
        if ($this->isLoaded()) {
            throw new RefactoringException(__('Cant add type default filter: collection is loaded'));
        }
        $this->getSelect()
             ->where('`main_table`.`' . FeeInterface::TYPE . '` = ?', FeeInterface::SHIPPING_TYPE);

        return $this;
    }
}
