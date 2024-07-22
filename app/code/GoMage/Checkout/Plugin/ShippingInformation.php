<?php

namespace GoMage\Checkout\Plugin;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;
use Psr\Log\LoggerInterface;

class ShippingInformation
{
    protected $quoteRepository;

    protected $dataHelper;
    protected LoggerInterface $logger;
    protected Session $session;

    /**
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        Session $session
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->session = $session;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @throws NoSuchEntityException
     */
    public function beforeSaveAddressInformation (
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    )
    {
        $extensionAttributes = $addressInformation->getShippingAddress()->getExtensionAttributes();
        $password      = $extensionAttributes->getCaPassword();
        $repassword = $extensionAttributes->getCaPasswordConfirmation();
        $this->session->setCaPassword($password);
        $this->session->setCaRePassword($repassword);
    }
}
