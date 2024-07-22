<?php

namespace Smartblinds\LazyLoad\Plugin;

use Magefan\LazyLoad\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\AbstractBlock;

class BlockPlugin extends \Magefan\LazyLoad\Plugin\BlockPlugin
{
    public function __construct(
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        Config $config
    ) {
        parent::__construct($request, $scopeConfig, $config);
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
    }

    public function afterToHtml(AbstractBlock $block, $html)
    {
        $html = (string) $html;
        if (!$this->isEnabled($block, $html)) {
            return $html;
        }

        $productsListBlocks = [
            'category.products.list',
            'search_result_list'
        ];
        if (in_array($block->getNameInLayout(), $productsListBlocks)) {
            $pixelImg = 'images/product-list-placeholder.png';
        } else {
            $pixelImg = 'Magefan_LazyLoad/images/pixel.jpg';
        }
        $pixelSrc = ' src="' . $block->getViewFileUrl($pixelImg) . '"';
        $tmpSrc = 'TMP_SRC';

        $html = str_replace($pixelSrc, $tmpSrc, $html);

        $noscript = '';
        if ($this->config->isNoScriptEnabled()) {
            $noscript = '<noscript>
                <img src="$2"  $1 $3  />
            </noscript>';
        }

        $html = preg_replace('#<img(?!.*mfdislazy).*([^>]*)(?:\ssrc="([^"]*)")([^>]*)\/?>#isU', '<img ' .
            ' data-original="$2" $1 $3/>
            ' . $noscript, $html);

        $html = str_replace(' data-original=', $pixelSrc . ' data-original=', $html);

        $html = str_replace($tmpSrc, $pixelSrc, $html);
        $html = str_replace(self::LAZY_TAG, '', $html);

        /* Disable Owl Slider LazyLoad */
        $html = str_replace(
            ['"lazyLoad":true,', '&quot;lazyLoad&quot;:true,', 'owl-lazy'],
            ['"lazyLoad":false,', '&quot;lazyLoad&quot;:false,', ''],
            $html
        );

        /* Fix for page builder bg images */
        if (false !== strpos($html, 'background-image-')) {
            $html = str_replace('.background-image-', '.tmpbgimg-', $html);
            $html = str_replace('background-image-', 'mflazy-background-image mflazy-background-image-', $html);
            $html = str_replace('.tmpbgimg-', '.background-image-', $html);
        }

        return $html;
    }
}
