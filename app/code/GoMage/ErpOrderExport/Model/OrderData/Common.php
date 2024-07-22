<?php

declare(strict_types=1);

namespace GoMage\ErpOrderExport\Model\OrderData;

use GoMage\ErpOrderExport\Model\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Model\Order\Address;

class Common implements DataProviderInterface
{
    private OrderAddressRepositoryInterface $addressRepository;
    private SearchCriteriaBuilder $criteriaBuilder;
    private Config $config;

    public function __construct(
        OrderAddressRepositoryInterface $addressRepository,
        SearchCriteriaBuilder $criteriaBuilder,
        Config $config
    ) {
        $this->addressRepository = $addressRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->config = $config;
    }

    public function getData(OrderInterface $order): array
    {
        $address = $this->loadShippingAddress($order);
        return [
            'increment_id' => $order->getIncrementId(),
            'order_type'   => $order->getData('order_type'),
            'base_increment_id'   => $order->getData('base_increment_id'),
            'reference'   => '',
            'country_code' => $address->getCountryId(),
            'firstname'    => $address->getFirstname(),
            'lastname'     => $address->getLastname(),
            'email'        => $address->getEmail(),
            'company'      => $address->getCompany(),
            'street'       => implode(' ', $this->prepareStreetData($order, $address)),
            'postcode'     => $address->getPostcode(),
            'city'         => $address->getCity(),
            'telephone'    => $address->getTelephone() ?: '+31851306287'
        ];
    }

    private function loadShippingAddress(OrderInterface $order): OrderAddressInterface
    {
        $this->criteriaBuilder
            ->addFilter(OrderAddressInterface::PARENT_ID, $order->getEntityId())
            ->addFilter(OrderAddressInterface::ADDRESS_TYPE, Address::TYPE_SHIPPING);
        $addresses = $this->addressRepository->getList($this->criteriaBuilder->create())->getItems();
        $address = reset($addresses);
        if (!$address) {
            throw new LocalizedException(__('Order without shipping address'));
        }
        return $address;
    }

    private function prepareStreetData($order, $address)
    {
        return $this->config->isReverseStreet($order->getStoreId()) ? array_reverse($address->getStreet()) : $address->getStreet();
    }
}
