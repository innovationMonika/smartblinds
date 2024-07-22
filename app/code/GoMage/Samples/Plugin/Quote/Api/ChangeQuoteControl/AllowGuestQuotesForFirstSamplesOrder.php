<?php declare(strict_types=1);

namespace GoMage\Samples\Plugin\Quote\Api\ChangeQuoteControl;

use GoMage\Samples\Model\Claim\PlaceOrder\CustomerCreator\Registry;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Api\ChangeQuoteControlInterface;
use Magento\Quote\Api\Data\CartInterface;

class AllowGuestQuotesForFirstSamplesOrder
{
    private UserContextInterface $userContext;
    private Registry $registry;

    public function __construct(
        UserContextInterface $userContext,
        Registry $registry
    ) {
        $this->userContext = $userContext;
        $this->registry = $registry;
    }

    public function afterIsAllowed(ChangeQuoteControlInterface $subject, bool $result, CartInterface $quote)
    {
        if ($result) {
            return $result;
        }

        $isGuestType = $this->userContext->getUserType() === UserContextInterface::USER_TYPE_GUEST;
        if ($isGuestType && $this->registry->hasCustomer($quote->getCustomerEmail())) {
            return true;
        }

        return $result;
    }
}
