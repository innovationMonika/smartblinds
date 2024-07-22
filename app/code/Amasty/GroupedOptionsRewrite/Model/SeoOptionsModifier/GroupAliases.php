<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Grouped Options for Magento 2
*/

declare(strict_types=1);

namespace Amasty\GroupedOptionsRewrite\Model\SeoOptionsModifier;

use Amasty\GroupedOptions\Api\Data\GroupAttrInterface;
use Amasty\GroupedOptions\Model\FakeKeyGenerator;
use Amasty\GroupedOptions\Model\GroupAttr\DataProvider;
use Amasty\ShopbySeo\Model\SeoOptionsModifier\UniqueBuilder;
use Magento\Store\Model\StoreManagerInterface;

class GroupAliases extends \Amasty\GroupedOptions\Model\SeoOptionsModifier\GroupAliases
{
    /**
     * @var UniqueBuilder|null
     */
    private $uniqueBuilder;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var FakeKeyGenerator
     */
    private $fakeKeyGenerator;

    protected $storeManager;

    public function __construct(
        DataProvider $dataProvider,
        FakeKeyGenerator $fakeKeyGenerator,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($dataProvider, $fakeKeyGenerator, $data);
        $this->uniqueBuilder = $data['uniqueBuilder'] ?? null;
        $this->dataProvider = $dataProvider;
        $this->fakeKeyGenerator = $fakeKeyGenerator;
        $this->storeManager = $storeManager;
    }

    public function modify(array &$optionsSeoData, int $storeId, array $attributeIds = []): void
    {
        foreach ($attributeIds as $id => $code) {
            $data = $this->getAliasGroup((int) $id);
            if ($data) {
                foreach ($data as $key => $record) {
                    if ($this->getUniqueBuilder()) {
                        $alias = $this->getUniqueBuilder()->execute($record[GroupAttrInterface::URL], (string) $key);
                    } else {
                        $alias = $record[GroupAttrInterface::URL];
                    }
                    $optionsSeoData[$storeId][$code][$record[GroupAttrInterface::GROUP_CODE]] = $alias;
                }
            }
        }
    }

    private function getAliasGroup(int $attributeId): array
    {
        $data = [];
        $groups = $this->dataProvider->getGroupsByAttributeId($attributeId);

        foreach ($groups as $group) {

            $storeDataJson = $group->getUrl();
             // Decode JSON string to associative array
            $storeData = !empty($storeDataJson) ? json_decode($storeDataJson, true) : '';

            // Get current store ID
            $currentStoreId = $this->storeManager->getStore()->getId();

            // Retrieve data for the current store
            $storeDataForCurrentStore = isset($storeData[$currentStoreId]) ? $storeData[$currentStoreId] : null;

            $id = $this->fakeKeyGenerator->generate((int) $group->getGroupId());
            $data[$id][GroupAttrInterface::GROUP_CODE] = $group->getGroupCode();
            $data[$id][GroupAttrInterface::URL] = $storeDataForCurrentStore ?: $group->getGroupCode();
        }

        return $data;
    }

    private function getUniqueBuilder(): ?UniqueBuilder
    {
        return $this->uniqueBuilder;
    }
}
