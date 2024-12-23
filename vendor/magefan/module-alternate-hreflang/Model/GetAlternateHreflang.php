<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model;

use Magefan\AlternateHreflang\Api\GetAlternateHreflangInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magefan\AlternateHreflang\Api\AlternateHreflangUrlsInterface;

class GetAlternateHreflang implements GetAlternateHreflangInterface
{
    /**
     * @var array
     */
    private array $modelPool;

    /**
     * @param array $modelPool
     */
    public function __construct(
        array $modelPool
    ) {
        $this->modelPool = $modelPool;
    }

    /**
     * @param $object
     * @param $type
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function execute($object, $type)
    {
        foreach ($this->modelPool as $modelType => $model) {
            if ($type === $modelType && $model instanceof AlternateHreflangUrlsInterface) {
                return $model->getAlternateUrls($object);
            }
        }

        throw new NoSuchEntityException(
                    __("The page type that was requested doesn't exist.")
        );
    }
}
