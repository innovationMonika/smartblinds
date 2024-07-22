<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Claim\PlaceOrder\Validator;

use GoMage\Samples\Api\Data\Claim\InfoInterface;

interface ValidatorInterface
{
    public function validate(InfoInterface $info);
}
