<?php declare(strict_types=1);

namespace GoMage\Samples\Api\Data\Claim\Info;

/**
 * @api
 */
interface ItemInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param int $id
     * @return \GoMage\Samples\Api\Data\Claim\Info\ItemInterface
     */
    public function setId(int $id);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return \GoMage\Samples\Api\Data\Claim\Info\ItemInterface
     */
    public function setName(string $name);
}
