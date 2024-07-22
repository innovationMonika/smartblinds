<?php declare(strict_types=1);

namespace Smartblinds\System\Plugin\ConfigurableProduct\Block\Product\View\Type\Configurable;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\Serialize\Serializer\Json;
use Smartblinds\System\Block\Product\Renderer\Configurable as ConfigurableBlock;

class AddAlternateSwatches
{
    private Json $json;
    private ConfigurableBlock $configurableBlock;

    public function __construct(
        Json $json,
        ConfigurableBlock $configurableBlock
    ) {
        $this->json = $json;
        $this->configurableBlock = $configurableBlock;
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

        $resultDecoded['alternateSwatches'] = $this->configurableBlock->getSwatchAlternateConfig();

        return $this->json->serialize($resultDecoded);
    }
}
