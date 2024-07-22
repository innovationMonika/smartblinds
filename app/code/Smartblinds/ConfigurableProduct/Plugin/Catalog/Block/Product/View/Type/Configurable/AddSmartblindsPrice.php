<?php declare(strict_types=1);

namespace Smartblinds\ConfigurableProduct\Plugin\Catalog\Block\Product\View\Type\Configurable;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class AddSmartblindsPrice
{
    private Json $json;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        Json $json,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->json = $json;
        $this->priceCurrency = $priceCurrency;
    }

    public function afterGetJsonConfig(
        Configurable $subject,
        $result
    ) {
        $product = $subject->getProduct();
        if ($product->getTypeId() !== ConfigurableType::TYPE_CODE) {
            return $result;
        }

        $resultDecoded = $this->json->unserialize($result);

        foreach ($subject->getAllowProducts() as $child) {
            $resultDecoded['optionPrices'][$child->getId()]['smartblindsPrice'] = [
                'amount' => $this->priceCurrency->convert((float) $child->getData('smartblinds_price'))
            ];
        }
        return $this->json->serialize($resultDecoded);
    }
}
