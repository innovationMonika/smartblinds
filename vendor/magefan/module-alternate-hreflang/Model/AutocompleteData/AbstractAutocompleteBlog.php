<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AlternateHreflang\Model\AutocompleteData;

use Magefan\AlternateHreflang\Api\BlogFactoryInterface;

abstract class AbstractAutocompleteBlog
{
    /**
     * @var BlogFactoryInterface
     */
    protected $blogFactory;

    /**
     * @param BlogFactoryInterface $blogFactory
     */
    public function __construct(
        BlogFactoryInterface $blogFactory
    )
    {
        $this->blogFactory = $blogFactory;
    }

    /**
     * @return string
     */
    abstract public function getIdFieldName(): string;

    /**
     * @return string
     */
    abstract public function getFunctionCreateCollectionName(): string;

    /**
     * @return string
     */
    public function getSecondFieldName(): string
    {
        return 'title';
    }

    /**
     * @param $search
     * @return array
     */
    public function getItems($search)
    {
        $functionCreateCollectionName = $this->getFunctionCreateCollectionName();
        $collection = $this->blogFactory->$functionCreateCollectionName();
        $secondFieldName =  $this->getSecondFieldName();
        $collection
            ->addFieldToFilter(
                [$this->getIdFieldName(), $secondFieldName],
                [
                    ['eq' => $search],
                    ['like' => '%' . $search . '%'],
                ]
            );

        $result = [];
        $itemGetSecondFieldMethod = 'get' . ucfirst($secondFieldName);
        foreach ($collection as $item) {
            $result[] = [
                'value' => $item->getId() . '. ' . $item->$itemGetSecondFieldMethod(),
                'label' => $item->getId() . '. ' . $item->$itemGetSecondFieldMethod()
            ];
        }

        return $result;
    }
}