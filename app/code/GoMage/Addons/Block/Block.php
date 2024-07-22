<?php

namespace GoMage\Addons\Block;

class Block extends \Magento\Cms\Block\Block
{
    protected function _toHtml()
    {
        $blockIds = $this->getBlockId();
        $html = '';
        if ($blockIds && is_array($blockIds)) {
            $storeId = $this->_storeManager->getStore()->getId();
            foreach ($blockIds as $blockId) {
                /** @var \Magento\Cms\Model\Block $block */
                $block = $this->_blockFactory->create();
                $block->setStoreId($storeId)->load($blockId);
                if ($block->isActive()) {
                    $html .= '<div class="pdp-tab-content-grid__item">'.$this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter(
                        $block->getContent()).'</div>';
                }
            }
        }

        return $html;
    }

    public function getIdentities()
    {
        return [\Magento\Cms\Model\Block::CACHE_TAG . '_' . implode("_", $this->getBlockId()??[])];
    }
}
