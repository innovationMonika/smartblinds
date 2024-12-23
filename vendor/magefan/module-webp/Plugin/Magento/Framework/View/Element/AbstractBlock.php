<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Plugin\Magento\Framework\View\Element;

use Magento\Framework\View\Element\AbstractBlock as AbstractBlockBase;
use Magento\Framework\App\RequestInterface;
use Magefan\WebP\Api\HtmlParserInterface as HtmlParser;
use Magefan\WebP\Model\Config;

/**
 * Plugin for AbstractBlock to parse Ajax request blocks
 */
class AbstractBlock
{

    /**
     * @var HtmlParser
     */
    private $htmlParser;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * AbstractBlock constructor.
     * @param RequestInterface $request
     * @param Config $config
     * @param HtmlParser $htmlParser
     */
    public function __construct(
        RequestInterface $request,
        Config $config,
        HtmlParser $htmlParser
    ) {
        $this->request = $request;
        $this->config = $config;
        $this->htmlParser = $htmlParser;
    }

    /**
     * @param AbstractBlockBase $block
     * @param $output
     * @return mixed
     */
    public function afterToHtml(AbstractBlockBase $block, ?string $output): ?string
    {
        if (!$output) {
            return $output;
        }

        /* Do not use this, some bad code in other extension can execute toHtml many times :-(
        if (false !== strpos($output, 'mf_webp')) {
            return $output;
        }
        */

        if (!$this->config->isEnabled()) {
            return $output;
        }

        $actionName = strtolower((string)$this->request->getActionName());
        $isAjax = ($this->request->isXmlHttpRequest()
            || false !== stripos($this->request->getFullActionName(), 'ajax')
            || false !== stripos((string)$this->request->getServer('REQUEST_URI'), 'layerednavigationajax')
            || $this->request->getParam('isAjax')
        );

        /*mgs theme compatibility */
        if (false === strpos(get_class($block), 'MGS\Promobanners')) {
            if (!$isAjax && $actionName != 'esi') {
                /* If not ajax request and not page_cache/block/esi request */
                return $output;
            }
        }

        $handles = $block->getLayout()->getUpdate()->getHandles();
        $blackListHandles = [
            'sales_email_order_invoice_items'
        ];

        /* Should be && and not || as in other plugin */
        if ($handles && array_intersect($blackListHandles, $handles)) {
            return $output;
        }

        return $this->htmlParser->execute($output);
    }
}
