<?php

namespace Smartblinds\Customer\Model;

use Magento\Config\Model\Config\Source\Nooptreq as NooptreqSource;

class Options extends \Magento\Customer\Model\Options
{
    public function getNamePrefixOptions($store = null)
    {
        return $this->prepareNamePrefixSuffixOptions(
            $this->addressHelper->getConfig('prefix_options', $store),
            $this->addressHelper->getConfig('prefix_show', $store) == NooptreqSource::VALUE_OPTIONAL
        );
    }

    public function getNameSuffixOptions($store = null)
    {
        return $this->prepareNamePrefixSuffixOptions(
            $this->addressHelper->getConfig('suffix_options', $store),
            $this->addressHelper->getConfig('suffix_show', $store) == NooptreqSource::VALUE_OPTIONAL
        );
    }

    private function prepareNamePrefixSuffixOptions($options, $isOptional = false)
    {
        $options = trim($options ?? '');
        if (empty($options)) {
            return false;
        }
        $result  = [];
        $options = explode(';', $options);
        foreach ($options as $value) {
            $value = $this->escaper->escapeHtml(trim($value));
            $result[(string)__($value)] = __($value);
        }
        if ($isOptional && trim(current($options))) {
            $result = array_merge([' ' => ' '], $result);
        }

        return $result;
    }
}
