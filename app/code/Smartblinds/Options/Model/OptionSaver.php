<?php

namespace Smartblinds\Options\Model;

class OptionSaver extends \MageWorx\OptionTemplates\Model\OptionSaver
{
    public function addNewOptionProcess(array $productOptions, $productSku, $group = null)
    {
        if ($group === null) {
            $groupOptions = $this->groupOptions;
        } else {
            $groupOptions = $this->groupEntity->getOptionsAsArray($group);
        }

        foreach ($groupOptions as $groupOption) {
            $issetGroupOptionInProduct = false;
            foreach ($productOptions as $optionIndex => &$productOption) {
                if (empty($productOption['group_option_id'])
                    || $productOption['group_option_id'] !== $groupOption['option_id']
                ) {
                    continue;
                }

                $issetGroupOptionInProduct = true;
                if (isset($groupOption['dependency'])) {
                    $this->attributeSaver->addNewGroupOptionIds($groupOption['option_id']);
                    $productOption['dependency']                 = $groupOption['dependency'];
                    $productOption['need_to_process_dependency'] = true;
                }

                if (empty($productOption['values']) || !is_array($productOption['values'])
                    || empty($groupOption['values']) || !is_array($groupOption['values'])
                ) {
                    continue;
                }

                foreach ($productOption['values'] as &$productOptionValue) {
                    foreach ($groupOption['values'] as $groupOptionValue) {
                        if(isset($groupOptionValue['load_linked_product'])) {
                            $productOptionValue['is_default'] = $this->setIsDefaultAttrForLLPLogic($productSku, $productOptionValue);
                        }
                        if (empty($productOptionValue['group_option_value_id'])
                            || $productOptionValue['group_option_value_id'] !== $groupOptionValue['option_type_id']
                            || !isset($groupOptionValue['dependency'])
                        ) {
                            continue;
                        }
                        $productOptionValue['dependency']                 = $groupOptionValue['dependency'];
                        $productOptionValue['need_to_process_dependency'] = true;
                        $productOptionValue['value_code'] = $groupOptionValue['value_code'] ?? null;
                        $productOptionValue['value_code_width'] = $groupOptionValue['value_code_width'] ?? null;
                        $productOptionValue['value_code_height'] = $groupOptionValue['value_code_height'] ?? null;
                        $productOptionValue['value_code_m2'] = $groupOptionValue['value_code_m2'] ?? null;

                        $this->attributeSaver->addNewGroupOptionIds($groupOption['option_id']);
                    }
                }
            }

            if (!$issetGroupOptionInProduct) {
                $groupOption['group_option_id'] = $groupOption['id'];
                if ($this->getIsTemplateSave()) {
                    $groupOption['id']                   = $this->currentIncrementIds['option'];
                    $groupOption['option_id']            = $groupOption['id'];
                    $this->currentIncrementIds['option'] += 1;
                } else {
                    $groupOption['id']        = null;
                    $groupOption['option_id'] = null;
                }
                $this->attributeSaver->addNewGroupOptionIds($groupOption['group_option_id']);
                $groupOption['need_to_process_dependency'] = true;

                if (!empty($groupOption['values'])) {
                    foreach ($groupOption['values'] as &$grOptValue) {
                        if(isset($grOptValue['load_linked_product'])) {
                            $grOptValue['is_default'] = $this->setIsDefaultAttrForLLPLogic($productSku, $grOptValue);
                        }
                    }
                }

                $groupOption                      = $this->convertGroupToProductOptionValues($groupOption);
                $productOptions[]                 = $groupOption;
                $this->productGroupNewOptionIds[] = $groupOption['group_option_id'];
            }
        }

        return $productOptions;
    }
}