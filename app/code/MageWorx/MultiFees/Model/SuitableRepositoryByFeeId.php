<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model;

use MageWorx\MultiFees\Api\CartFeeRepositoryInterface;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Api\PaymentFeeRepositoryInterface;
use MageWorx\MultiFees\Api\ProductFeeRepositoryInterface;
use MageWorx\MultiFees\Api\ShippingFeeRepositoryInterface;
use MageWorx\MultiFees\Exception\RefactoringException;

class SuitableRepositoryByFeeId
{
    /**
     * @var CartFeeRepositoryInterface[]|PaymentFeeRepositoryInterface[]|ShippingFeeRepositoryInterface[]
     */
    protected $suitableRepositoriesByFeeId;

    /**
     * @var CartFeeRepositoryInterface
     */
    protected $cartFeeRepository;

    /**
     * @var ShippingFeeRepositoryInterface
     */
    protected $shippingFeeRepository;

    /**
     * @var PaymentFeeRepositoryInterface
     */
    protected $paymentFeeRepository;

    /**
     * @var ProductFeeRepositoryInterface
     */
    protected $productFeeRepository;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $connection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * SuitableRepositoryByFeeId constructor.
     *
     * @param CartFeeRepositoryInterface $cartFeeRepository
     * @param ShippingFeeRepositoryInterface $shippingFeeRepository
     * @param PaymentFeeRepositoryInterface $paymentFeeRepository
     * @param ProductFeeRepositoryInterface $productFeeRepository
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        CartFeeRepositoryInterface $cartFeeRepository,
        ShippingFeeRepositoryInterface $shippingFeeRepository,
        PaymentFeeRepositoryInterface $paymentFeeRepository,
        ProductFeeRepositoryInterface $productFeeRepository,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection         = $resourceConnection->getConnection(
            \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
        );

        $this->cartFeeRepository     = $cartFeeRepository;
        $this->shippingFeeRepository = $shippingFeeRepository;
        $this->paymentFeeRepository  = $paymentFeeRepository;
        $this->productFeeRepository  = $productFeeRepository;
    }

    /**
     * Returns suitable repository gor the fee using its ID
     * Algorithm: get fee type by its id using query to database
     * then find suitable repository by its type
     *
     * @param int $feeId
     * @return CartFeeRepositoryInterface|PaymentFeeRepositoryInterface|ShippingFeeRepositoryInterface
     * @throws RefactoringException
     */
    public function get($feeId)
    {
        if (!empty($this->suitableRepositoriesByFeeId[$feeId])) {
            return $this->suitableRepositoriesByFeeId[$feeId];
        }

        $select = $this->connection->select();
        $select->from($this->resourceConnection->getTableName('mageworx_multifees_fee'), [FeeInterface::TYPE]);
        $select->where('fee_id = ' . $feeId);
        $type = $this->connection->fetchOne($select);

        if (!$type) {
            throw new RefactoringException(__('Cant find type for the fee with id %1', $feeId));
        }

        $this->suitableRepositoriesByFeeId[$feeId] = $this->getSuitableFeeRepositoryByType($type);

        return $this->suitableRepositoriesByFeeId[$feeId];
    }

    /**
     * Find and return repository by fee type
     *
     * @param int $type
     * @return CartFeeRepositoryInterface|PaymentFeeRepositoryInterface|ShippingFeeRepositoryInterface
     * @throws RefactoringException
     */
    public function getSuitableFeeRepositoryByType($type)
    {
        switch ($type) {
            case FeeInterface::CART_TYPE:
                $repository = $this->cartFeeRepository;
                break;
            case FeeInterface::SHIPPING_TYPE:
                $repository = $this->shippingFeeRepository;
                break;
            case FeeInterface::PAYMENT_TYPE:
                $repository = $this->paymentFeeRepository;
                break;
            case FeeInterface::PRODUCT_TYPE:
                $repository = $this->productFeeRepository;
                break;
            default:
                throw new RefactoringException(__('Unspecified fee type to detect suitable repository #261840'));
        }

        return $repository;
    }
}
