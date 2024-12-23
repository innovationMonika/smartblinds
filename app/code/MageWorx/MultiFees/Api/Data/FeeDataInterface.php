<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Api\Data;

/**
 * Interface FeeDataInterface
 *
 * @api
 */
interface FeeDataInterface
{
    /**
     * @param int $id
     * @return FeeDataInterface
     */
    public function setId(int $id): FeeDataInterface;

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param string $options
     * @return FeeDataInterface
     */
    public function setOptions(string $options): FeeDataInterface;

    /**
     * @return string|null
     */
    public function getOptions(): ?string;

    /**
     * @param string $message
     * @return FeeDataInterface
     */
    public function setMessage(string $message): FeeDataInterface;

    /**
     * @return string|null
     */
    public function getMessage(): ?string;

    /**
     * @param string $date
     * @return FeeDataInterface
     */
    public function setDate(string $date): FeeDataInterface;

    /**
     * @return string|null
     */
    public function getDate(): ?string;

    /**
     * @param \MageWorx\MultiFees\Api\Data\FeeDataInterface[] $multipleData
     * @return FeeDataInterface
     */
    public function setMultipleData(array $multipleData): FeeDataInterface;

    /**
     * @return \MageWorx\MultiFees\Api\Data\FeeDataInterface[]|null
     */
    public function getMultipleData(): ?array;

    /**
     * @return mixed[]
     */
    public function getData(): array;
}
