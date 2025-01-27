<?php
declare(strict_types=1);

namespace Smartblinds\Conversions\Plugin\Samples\Model\Claim\PlaceOrder;

use GoMage\Samples\Api\Data\Claim\InfoInterface;
use GoMage\Samples\Model\Claim\PlaceOrder\CartCollector as SamplesCartCollector;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class CartCollector
{
    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->cartRepository = $cartRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Set conversion data
     *
     * @param SamplesCartCollector $subject
     * @param $cartId
     * @param InfoInterface $info
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterCollect(SamplesCartCollector $subject, $cartId, InfoInterface $info)
    {
        $cart = $this->cartRepository->get($cartId);
        $cart->setStoreId($this->storeManager->getStore()->getId());

        $cart->setGclid($info->getGclid() ?? null);
        $cart->setFbp($info->getFbp() ?? null);
        $cart->setFbc($info->getFbc() ?? null);
        $this->cartRepository->save($cart);

        return $cartId;
    }
}
