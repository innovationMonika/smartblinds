<?php declare(strict_types=1);

namespace Smartblinds\Cms\ViewModel;

use Magento\Catalog\Block\Product\View\Options;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use \Magento\Framework\Registry;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Cms implements ArgumentInterface
{
    const XML_DEFAULT_CONTROL_TYPE_NOTICE = 'smartblinds_cms/system_control_type_notice/notice_default';
    const XML_DEFAULT_CONTROL_TYPE_NOTICE_LABEL = 'smartblinds_cms/system_control_type_notice/notice_label_default';
    const XML_CONTROL_TYPE_NOTICES = 'smartblinds_cms/system_control_type_notice/notices';

    private StoreManagerInterface $storeManager;
    private BlockFactory $blockFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param BlockFactory $blockFactory
     * @param Registry $coreRegistry
     * @param Json $json
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        Registry $coreRegistry,
        Json $json
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->blockFactory = $blockFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->json = $json;

    }

    /**
     * Return current category object
     *
     * @return \Magento\Catalog\Model\Category|null
     */
    protected function _getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * @param string $path
     * @return array
     */
    protected function _unserializeConfig(string $path): array
    {
        try {
            $config = $this->json->unserialize($path);
            if (!is_array($config)) {
                throw new \InvalidArgumentException();
            }
        } catch (\InvalidArgumentException $e) {
            return [];
        }
        return array_values($config);
    }

    /**
     * @return array
     */
    protected function getDefaultControlTypeNoticeBlockData()
    {
        return [
            'label' => $this->_scopeConfig->getValue(self::XML_DEFAULT_CONTROL_TYPE_NOTICE_LABEL),
            'blockId' => $this->_scopeConfig->getValue(self::XML_DEFAULT_CONTROL_TYPE_NOTICE)
        ];
    }

    /**
     * @return array
     */
    protected function _getControlTypeNoticesBlocksData()
    {
        $values = [];
        if ($config = $this->_scopeConfig->getValue(self::XML_CONTROL_TYPE_NOTICES)) {
            $config = $this->_unserializeConfig($config);
            if (!empty($config)) {
                foreach ($config as $item) {
                    $values[$item['control_type']] = [
                        'label' => $item['label'] ?? '',
                        'blockId' => $item['cms_block'] ?? ''
                    ];
                }
            }
        }
        return $values;
    }

    /**
     * @return array|null|string
     */
    protected function _getProductControlType()
    {
        $controlType = null;
        if ($product = $this->_getProduct()) {
            if ($this->_getProduct()->getTypeId() === Configurable::TYPE_CODE) {
                if ($children = $product->getTypeInstance()->getUsedProducts($product)) {
                    $controlType = [];
                    foreach ($children as $child) {
                        $controlType[] = $child->getData('control_type');
                        $controlType = array_unique($controlType);
                    }
                }
            } else {
                $controlType = $product->getData('control_type');
            }
        }
        return $controlType;
    }

    /**
     * @return array|null
     */
    public function getProductBlockData()
    {
        $controlType = $this->_getProductControlType();
        if (is_array($controlType) && count($controlType) > 1) {
            return $this->getDefaultControlTypeNoticeBlockData();
        } else if ($controlType) {
            $controlType = is_array($controlType) ? $controlType[0] : $controlType;
            $blocks  = $this->_getControlTypeNoticesBlocksData();
            return $blocks[$controlType] ?? $this->getDefaultControlTypeNoticeBlockData();
        }
        return null;
    }

    public function getBlockTitle(string $blockId): ?string
    {
        $storeId = $this->storeManager->getStore()->getId();
        /** @var Block $block */
        $block = $this->blockFactory->create();
        $block->setStoreId($storeId)->load($blockId);
        if ($block->isActive()) {
            return $block->getTitle();
        }
        return null;
    }

    public function getModalsData(Options $block): array
    {
        return array_merge(
            $this->prepareOptionModals($block),
            $this->prepareSwatchModals($block)
        );
    }

    private function prepareOptionModals(Options $block): array
    {
        $result = [];
        $blockIds = [];
        $options = $block->decorateArray($block->getOptions());
        foreach ($options as $option) {
            $blockIds[] = $option->getData('modal_code');
        }
        foreach ($blockIds as $blockId) {
            /** @var Block $cmsBlock */
            $cmsBlock = $this->loadCmsBlock($blockId);
            $result[] = $this->fillBlockData($block, $blockId, $cmsBlock);
        }
        return $result;
    }

    private function prepareSwatchModals(Options $block): array
    {
        $result = [];
        $product = $block->getProduct();
        $layoutOptions = array_values($block->getData('swatch_options'));
        foreach ($layoutOptions as $swatchAttributeCode) {

            $systemCategory = $product->getData('system_category');
            if (!$systemCategory) {
                $cmsBlock = $this->loadCmsBlock($swatchAttributeCode);
                $result[] = $this->fillBlockData($block, $swatchAttributeCode, $cmsBlock);
                continue;
            }

            $systemCategoryBlockId = "{$swatchAttributeCode}_{$systemCategory}";
            $cmsBlock = $this->loadCmsBlock($systemCategoryBlockId);
            if ($cmsBlock->getId()) {
                $result[] = $this->fillBlockData($block, $swatchAttributeCode, $cmsBlock);
                continue;
            }

            $cmsBlock = $this->loadCmsBlock($swatchAttributeCode);
            $result[] = $this->fillBlockData($block, $swatchAttributeCode, $cmsBlock);
        }
        return $result;
    }

    private function loadCmsBlock($blockId)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $cmsBlock = $this->blockFactory->create();
        $cmsBlock->setStoreId($storeId)->load($blockId);
        return $cmsBlock;
    }

    private function fillBlockData($block, $code, $cmsBlock)
    {
        return [
            'code'    => $code,
            'title'   => $cmsBlock->isActive() ? $cmsBlock->getTitle() : null,
            'content' => $block->getLayout()
                ->createBlock(\Magento\Cms\Block\Block::class)
                ->setBlockId($cmsBlock->getBlockId())
                ->toHtml()
        ];
    }
}
