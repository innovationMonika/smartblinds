<?php declare(strict_types=1);

namespace Smartblinds\Swatches\Block\Product\Renderer;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;
use Magento\Swatches\Model\SwatchAttributesProvider;
use Smartblinds\Swatches\Model\Config;
use Smartblinds\System\Model\Config as SystemConfig;

class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    protected $configScoupe;
    protected $customerSession;
    private SystemConfig $systemConfig;

    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        Session $customerSession,
        SystemConfig $systemConfig,
        array $data = [],
        SwatchAttributesProvider $swatchAttributesProvider = null,
        UrlBuilder $imageUrlBuilder = null
    ) {
        parent::__construct($context, $arrayUtils, $jsonEncoder, $helper, $catalogProduct, $currentCustomer,
            $priceCurrency,
            $configurableAttributeData, $swatchHelper, $swatchMediaHelper, $data, $swatchAttributesProvider,
            $imageUrlBuilder);
        $this->configScoupe = $context->getScopeConfig();
        $this->customerSession = $customerSession;
        $this->systemConfig = $systemConfig;
    }

    const SWATCH_IMAGE_LARGE = 'swatch_image_large';
    const SWATCH_IMAGE_LARGE_NAME = 'swatchImageLarge';

    protected function extractNecessarySwatchData(array $swatchDataArray)
    {
        $result['type'] = $swatchDataArray['type'];

        $isLargeSwatch = in_array($swatchDataArray['swatch_id'], $this->getConfig()->getLargeSwatchesIds());
        if ($result['type'] == Swatch::SWATCH_TYPE_VISUAL_IMAGE && !empty($swatchDataArray['value'])) {
            $result['value'] = $this->swatchMediaHelper->getSwatchAttributeImage(
                $isLargeSwatch ? self::SWATCH_IMAGE_LARGE : Swatch::SWATCH_IMAGE_NAME,
                $swatchDataArray['value']
            );
            $result['thumb'] = $this->swatchMediaHelper->getSwatchAttributeImage(
                Swatch::SWATCH_THUMBNAIL_NAME,
                $swatchDataArray['value']
            );
            if ($isLargeSwatch) {
                $result['size_code'] = self::SWATCH_IMAGE_LARGE_NAME;
            }
        } else {
            $result['value'] = $swatchDataArray['value'];
        }

        return $result;
    }

    public function getJsonSwatchSizeConfig()
    {
        $imageConfig = $this->swatchMediaHelper->getImageConfig();
        $sizeConfig = [];

        $sizeConfig[self::SWATCH_IMAGE_NAME]['width'] = $imageConfig[Swatch::SWATCH_IMAGE_NAME]['width'];
        $sizeConfig[self::SWATCH_IMAGE_NAME]['height'] = $imageConfig[Swatch::SWATCH_IMAGE_NAME]['height'];
        $sizeConfig[self::SWATCH_THUMBNAIL_NAME]['height'] = $imageConfig[Swatch::SWATCH_THUMBNAIL_NAME]['height'];
        $sizeConfig[self::SWATCH_THUMBNAIL_NAME]['width'] = $imageConfig[Swatch::SWATCH_THUMBNAIL_NAME]['width'];

        $sizeConfig[self::SWATCH_IMAGE_LARGE_NAME]['width'] = $imageConfig[self::SWATCH_IMAGE_LARGE]['width'];
        $sizeConfig[self::SWATCH_IMAGE_LARGE_NAME]['height'] = $imageConfig[self::SWATCH_IMAGE_LARGE]['height'];

        return $this->jsonEncoder->encode($sizeConfig);
    }

    private function getConfig(): Config
    {
        return ObjectManager::getInstance()->get(Config::class);
    }

    public function isShowControlType()
    {
        return $this->systemConfig->isShowControlType();
    }
}
