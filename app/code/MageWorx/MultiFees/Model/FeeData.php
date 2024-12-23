<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Model;

use MageWorx\MultiFees\Api\Data\FeeDataInterface;

class FeeData implements FeeDataInterface
{
    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $options;

    /**
     * @var string|null
     */
    protected $message;

    /**
     * @var string|null
     */
    protected $date;

    /**
     * @var array|null
     */
    protected $multipleData;

    /**
     * @return array
     */
    public function getData(): array
    {
        $data = [];

        if ($this->multipleData) {
            foreach ($this->multipleData as $feeData) {
                $data[] = [
                    'id'      => $feeData->getId(),
                    'options' => $feeData->getOptions(),
                    'message' => $feeData->getMessage(),
                    'date'    => $feeData->getDate()
                ];
            }
        } else {
            $data[] = [
                'id'      => $this->getId(),
                'options' => $this->getOptions(),
                'message' => $this->getMessage(),
                'date'    => $this->getDate()
            ];
        }

        return $data;
    }

    /**
     * @param int $id
     * @return FeeDataInterface
     */
    public function setId(int $id): FeeDataInterface
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $options
     * @return FeeDataInterface
     */
    public function setOptions(string $options): FeeDataInterface
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOptions(): ?string
    {
        return $this->options;
    }

    /**
     * @param \MageWorx\MultiFees\Api\Data\FeeDataInterface[] $multipleData
     * @return FeeDataInterface
     */
    public function setMultipleData(array $multipleData): FeeDataInterface
    {
        $this->multipleData = $multipleData;

        return $this;
    }

    /**
     * @return \MageWorx\MultiFees\Api\Data\FeeDataInterface[]|null
     */
    public function getMultipleData(): ?array
    {
        return $this->multipleData;
    }

    /**
     * @param string $message
     * @return FeeDataInterface
     */
    public function setMessage(string $message): FeeDataInterface
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $date
     * @return FeeDataInterface
     */
    public function setDate(string $date): FeeDataInterface
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDate(): ?string
    {
        return $this->date;
    }
}
