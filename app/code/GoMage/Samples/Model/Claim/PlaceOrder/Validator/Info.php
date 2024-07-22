<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Claim\PlaceOrder\Validator;

use GoMage\Samples\Api\Data\Claim\InfoInterface;
use GoMage\Samples\Exception\Claim\PlaceOrderException;
use Magento\Framework\Reflection\DataObjectProcessor;

class Info implements ValidatorInterface
{
    private DataObjectProcessor $dataObjectProcessor;

    public function __construct(DataObjectProcessor $dataObjectProcessor)
    {
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    public function validate(InfoInterface $info)
    {
        $values = $this->dataObjectProcessor->buildOutputDataArray($info, InfoInterface::class);
        unset($values['create_account']);
        unset($values['telephone']);
        unset($values['middlename']);
        unset($values['apartment']);
        unset($values['password']);

        foreach ($values as $value) {
            if (!$value) {
                throw new PlaceOrderException(__('Please fill all required fields'));
            }
        }
    }
}
