<?php declare(strict_types=1);

namespace Smartblinds\System\Block\Product\Renderer;

use Magento\Framework\App\ObjectManager;
use Smartblinds\System\Model\Config;

class Configurable extends \Smartblinds\Swatches\Block\Product\Renderer\Configurable
{
    protected function getConfigurableOptionsIds(array $attributeData)
    {
        $ids = [];
        foreach ($attributeData as $attributeDatum) {
            $ids = array_merge($ids, array_keys($attributeDatum['options'] ?? []));
        }
        return $ids;
    }

    public function getSwatchAlternateConfig()
    {
        $config = parent::getJsonSwatchConfig();
        $config = json_decode($config, true);

        $alternateOptions = [];
        foreach ($config as $attributeId => $content) {
            foreach ($content as $key => $value) {
                if (is_numeric($key)) {
                    $alternateOptions[$key] = $value;
                }
            }
        }

        $result = [];
        $result['options'] = $alternateOptions;
        $systemCategory = $this->getProduct()->getSystemCategory();
        $result['mapping'] = $this->getConfig()->getAlternateMapping($systemCategory);

        return $result;
    }

    private function getConfig(): Config
    {
        return ObjectManager::getInstance()->get(Config::class);
    }
}
