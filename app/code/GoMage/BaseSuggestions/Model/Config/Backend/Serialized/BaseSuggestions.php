<?php declare(strict_types=1);

namespace GoMage\BaseSuggestions\Model\Config\Backend\Serialized;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\Exception\LocalizedException;

class BaseSuggestions extends ArraySerialized
{
    public function beforeSave()
    {
        $suggestions = $this->getValue();
        if (!is_array($suggestions)) {
            return parent::beforeSave();
        }

        foreach ($suggestions as &$suggestion) {
            if (!is_array($suggestion)) {
                continue;
            }
            $url = $suggestion['url'] ?? '';
            preg_match('/https?:\/\//', $url, $matches);
            if ($matches) {
                throw new LocalizedException(__('Please use only relative paths in urls'));
            }
            $suggestion['url'] = ltrim($url, '/');
        }
        $this->setValue($suggestions);
        return parent::beforeSave();
    }
}
