<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Claim\PlaceOrder\CustomerCreator;

class Registry
{
    private array $customers = [];

    public function hasCustomer(string $email): bool
    {
        return isset($this->customers[$email]);
    }

    public function addCustomer(string $email)
    {
        $this->customers[$email] = true;
    }
}
