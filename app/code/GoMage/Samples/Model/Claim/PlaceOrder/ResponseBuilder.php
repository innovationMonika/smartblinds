<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Claim\PlaceOrder;

use GoMage\Samples\Api\Data\Claim\ResultInterfaceFactory;

class ResponseBuilder
{
    private ResultInterfaceFactory $resultFactory;

    public function __construct(ResultInterfaceFactory $resultFactory)
    {
        $this->resultFactory = $resultFactory;
    }

    public function buildSuccess(string $message)
    {
        return $this->resultFactory->create(['data' => [
            'success'  => true,
            'message' => __($message)
        ]]);
    }

    public function buildError(string $message)
    {
        return $this->resultFactory->create(['data' => [
            'success'  => false,
            'message' => __($message)
        ]]);
    }
}
