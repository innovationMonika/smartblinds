<?php declare(strict_types=1);

namespace Smartblinds\Options\Plugin\Catalog\Block\Product\View\Type\Configurable;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Serialize\Serializer\Json;
use Smartblinds\Options\Model\Config;

class AddData
{
    private Json $json;
    private Config $config;

    public function __construct(
        Json $json,
        Config $config
    ) {
        $this->json = $json;
        $this->config = $config;
    }

    public function afterGetJsonConfig(
        Configurable $subject,
        $result
    ) {
        $product = $subject->getProduct();

        $resultDecoded = $this->json->unserialize($result);

        $resultDecoded['systemTypeTdbuOptionId'] = $this->config->getSystemTypeTdbuOptionId();
        $resultDecoded['bedieningOptionCode'] = $this->config->getBedieningOptionCode();

        return $this->json->serialize($resultDecoded);
    }
}
