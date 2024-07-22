<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Claim;

use GoMage\Samples\Api\Claim\PlaceOrderInterface;
use GoMage\Samples\Api\Data\Claim\InfoInterface;
use GoMage\Samples\Api\Data\Claim\ResultInterface;
use GoMage\Samples\Exception\Claim\PlaceOrderException;
use GoMage\Samples\Model\Claim\PlaceOrder\CartCollector;
use GoMage\Samples\Model\Claim\PlaceOrder\ResponseBuilder;
use GoMage\Samples\Model\Claim\PlaceOrder\Validator\ValidatorInterface;
use GoMage\Samples\Model\ResourceModel\OrderUpdater;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class PlaceOrder implements PlaceOrderInterface
{
    private ResponseBuilder $responseBuilder;
    private CartManagementInterface $cartManagement;
    private ValidatorInterface $validator;
    private CartCollector $cartCollector;
    private OrderRepositoryInterface $orderRepository;
    private OrderUpdater $orderUpdater;

    public function __construct(
        ResponseBuilder $responseBuilder,
        CartManagementInterface $cartManagement,
        ValidatorInterface $validator,
        CartCollector $cartCollector,
        OrderRepositoryInterface $orderRepository,
        OrderUpdater $orderUpdater
    ) {
        $this->responseBuilder = $responseBuilder;
        $this->cartManagement = $cartManagement;
        $this->validator = $validator;
        $this->cartCollector = $cartCollector;
        $this->orderRepository = $orderRepository;
        $this->orderUpdater = $orderUpdater;
    }

    public function place(InfoInterface $info): ResultInterface
    {
        try {
            $this->validator->validate($info);
            $cartId = $this->cartCollector->collect($info);
        } catch (PlaceOrderException $e) {
            return $this->responseBuilder->buildError($e->getMessage());
        }

        $orderId = $this->cartManagement->placeOrder($cartId);
        $this->orderUpdater->update($orderId);

        $order = $this->orderRepository->get($orderId);
        return $this->responseBuilder->buildSuccess((string) $order->getIncrementId());
    }
}
