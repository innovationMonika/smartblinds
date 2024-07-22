<?php

declare(strict_types=1);

namespace GoMage\ErpOrderExport\Model;

use GoMage\ErpOrderExport\Model\Source\Status;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderProvider
{
    protected FilterBuilder $filterBuilder;
    protected FilterGroupBuilder $filterGroupBuilder;
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $criteriaBuilder;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * @return OrderInterface[]
     */
    public function loadOrders(): array
    {
        $this->criteriaBuilder
            ->addFilter('smartblinds_registration_status', Status::UNEXPORTED)
            ->addFilter('base_total_due', 0)
            ->addFilter(
                OrderInterface::STATUS,
                [
                    Order::STATE_HOLDED,
                    Order::STATE_COMPLETE,
                    Order::STATE_CLOSED,
                    Order::STATE_CANCELED,
                    'samples'
                ],
                'nin'
            );
        return array_reverse(
            $this->orderRepository->getList($this->criteriaBuilder->create())->getItems()
        );
    }

    /**
     * @return OrderInterface[]
     */
    public function loadSentOrders(): array
    {
        $this->criteriaBuilder
            ->addFilter(
                'smartblinds_registration_status',
                Status::ACCEPTED
            )
            ->addFilter(
                OrderInterface::STATUS,
                Order::STATE_COMPLETE,
                'neq'
            );
        $criteria = $this->criteriaBuilder->create();
        return $this->orderRepository->getList($criteria)->getItems();
    }
}
