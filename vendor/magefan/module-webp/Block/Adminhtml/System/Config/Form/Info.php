<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Block\Adminhtml\System\Config\Form;

/**
 * Admin webp configurations information block
 */
class Info extends \Magefan\Community\Block\Adminhtml\System\Config\Form\Info
{
    /**
     * Return extension url
     * @return string
     */
    protected function getModuleUrl(): string
    {
        return 'https://mage' . 'fan.com?utm_source=m2admin_webp_config&utm_medium=link&utm_campaign=regular';
    }

    /**
     * Return extension title
     * @return string
     */
    protected function getModuleTitle(): string
    {
        return 'WebP Optimized Images Extension';
    }

    /**
     * Return info block html
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element): string
    {
        $html = parent::render($element);

        $gdSupport = $this->isGdSupport();
        if (true !== $gdSupport) {
            $html .= '<div class="message message-warning">'
            .__('Your GD extension does not satisfy WebP requirements, '
                . 'please install or update your GD extension for PHP (<strong>%1</strong>).', $gdSupport)
            . '</div>';
        }

        return $html;
    }

    /**
     * Return true if gd support is enabled
     * @return bool
     */
    private function isGdSupport()
    {
        if (!function_exists('gd_info')) {
            return 'GD library is not enabled';
        }

        if (!function_exists('imagecreatefromwebp')) {
            return 'imagecreatefromwebp function does not exist';
        }

        foreach (gd_info() as $key => $value) {
            if (stristr($key, 'webp')) {
                return true;
            }
        }

        return 'WebP is not enabled in PHP GD library';
    }
}
