<?php

namespace Smartblinds\AddToCart\Block\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Cms\Model\BlockRepository;
use Magento\Store\Model\ScopeInterface;

/**
 * Product View block
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class View extends \Magento\Catalog\Block\Product\View
{
    /**
     * @var BlockRepository
     */
    protected $blockRepository;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        BlockRepository $blockRepository,
        array $data = []
    ) {
        parent::__construct($context, $urlEncoder, $jsonEncoder, $string, $productHelper, $productTypeConfig,
            $localeFormat, $customerSession, $productRepository, $priceCurrency, $data);
        $this->blockRepository = $blockRepository;
    }

    public function getBlockContent()
    {
        $cmsBlockId = $this->_scopeConfig->getValue("add_to_cart/general/discount_cms_block", ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getCode());
        try {
            $cmsBlockContent = $this->blockRepository->getById($cmsBlockId)->getContent();
        } catch (\Exception $e) {
            $cmsBlockContent = '';
        }

        return $cmsBlockContent;
    }

    public function getProductDataForATCWidget()
    {
        return json_encode([
            'product_type' => $this->getProduct()->getTypeId(),
        ]);
    }
}
