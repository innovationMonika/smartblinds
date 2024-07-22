<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Claim\PlaceOrder\Validator;

use GoMage\Samples\Api\Data\Claim\InfoInterface;

class CompositeValidator implements ValidatorInterface
{
    /** @var ValidatorInterface[] */
    private array $validators;

    public function __construct(array $validators = [])
    {
        $this->validators = $validators;
    }

    public function validate(InfoInterface $info)
    {
        foreach ($this->validators as $validator) {
            $validator->validate($info);
        }
    }
}
