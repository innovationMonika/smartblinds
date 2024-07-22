<?php
/**
 * Copyright © 2018 Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\PageHierarchy\Block;

class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @param $string
     * @param bool $escapeSingleQuote
     * @return string
     */
    public function escapeHtmlAttr($string, $escapeSingleQuote = true)
    {
        return $this->_escaper->escapeHtmlAttr((string)$string);
    }

    /**
     * @param $string
     * @return string
     */
    public function escapeJs($string)
    {
        $string = (string)$string;

        if ($string === '' || ctype_digit($string)) {
            return $string;
        }

        return preg_replace_callback(
            '/[^a-z0-9,\._]/iSu',
            function ($matches) {
                $chr = $matches[0];
                if (strlen($chr) != 1) {
                    $chr = mb_convert_encoding($chr, 'UTF-16BE', 'UTF-8');
                    $chr = ($chr === false) ? '' : $chr;
                }
                return sprintf('\\u%04s', strtoupper(bin2hex($chr)));
            },
            $string
        );
    }

    /**
     * Instead of using deprecated class
     *
     * @param $data
     * @return string
     */
    protected function jsonEncode($data)
    {
        $result = json_encode($data);
        if (false === $result) {
            $result = json_encode([]);
        }
        return $result;
    }
}
