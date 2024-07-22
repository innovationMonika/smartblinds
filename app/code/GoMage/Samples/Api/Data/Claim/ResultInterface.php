<?php declare(strict_types=1);

namespace GoMage\Samples\Api\Data\Claim;

/**
 * @api
 */
interface ResultInterface
{
    /**
     * @return bool
     */
    public function getSuccess(): bool;

    /**
     * @param bool $success
     * @return \GoMage\Samples\Api\Data\Claim\ResultInterface
     */
    public function setSuccess(bool $success);

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param string $message
     * @return \GoMage\Samples\Api\Data\Claim\ResultInterface
     */
    public function setMessage(string $message);
}
