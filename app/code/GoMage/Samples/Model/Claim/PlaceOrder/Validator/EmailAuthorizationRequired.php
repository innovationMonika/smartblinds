<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Claim\PlaceOrder\Validator;

use GoMage\Samples\Api\Data\Claim\InfoInterface;
use GoMage\Samples\Exception\Claim\PlaceOrderException;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class EmailAuthorizationRequired implements ValidatorInterface
{
    private UserContextInterface $userContext;
    private CustomerRepositoryInterface $customerRepository;

    public function __construct(
        UserContextInterface $userContext,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->userContext = $userContext;
        $this->customerRepository = $customerRepository;
    }

    public function validate(InfoInterface $info)
    {
        $isGuestType = $this->userContext->getUserType() === UserContextInterface::USER_TYPE_GUEST;
        if ($this->getCustomer($info->getEmail()) && $isGuestType) {
            throw new PlaceOrderException(__('Please log in to make an order with this email'));
        }
    }

    private function getCustomer(string $email): ?CustomerInterface
    {
        try {
            return $this->customerRepository->get($email);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
