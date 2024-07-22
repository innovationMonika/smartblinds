<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Data\Claim;

use GoMage\Samples\Api\Data\Claim\ResultInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class Result extends AbstractSimpleObject implements ResultInterface
{
    private const SUCCESS  = 'success';
    private const MESSAGE = 'message';

    public function getSuccess(): bool
    {
        return $this->_get(self::SUCCESS);
    }

    public function setSuccess(bool $success)
    {
        return $this->setData(self::SUCCESS, $success);
    }

    public function getMessage(): string
    {
        return (string) $this->_get(self::MESSAGE);
    }

    public function setMessage(string $message)
    {
        return $this->setData(self::MESSAGE, $message);
    }
}
