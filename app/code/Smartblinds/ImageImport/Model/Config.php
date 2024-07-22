<?php declare(strict_types=1);

namespace Smartblinds\ImageImport\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Config
{
    private ScopeConfigInterface $scopeConfig;
    private Json $json;

    private array $imagesConfig;
    private array $systemConfigs = [];

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $json
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
    }

    public function getSystemAttributeOptionId(string $attribute, $value)
    {
        $systemConfig = $this->systemConfigs[$attribute] ?? null;
        if (!$systemConfig) {
            $systemConfig = $this->getConfig("system/$attribute");
            $this->systemConfigs[$attribute] = $systemConfig;
        }
        foreach ($systemConfig as $row) {
            $rowValue = $row['value'] ?? null;
            $rowOptionId = $row['option'] ?? null;
            if ($value == $rowValue) {
                return $rowOptionId;
            }
        }
        return null;
    }

    public function getImagePosition($image)
    {
        $imagesConfig = $this->getImagesConfig();
        foreach ($imagesConfig as $imageConfig) {
            $rowImage = $imageConfig['image'] ?? null;
            $rowPosition = $imageConfig['position'] ?? null;
            if ($image == $rowImage) {
                return $rowPosition;
            }
        }
        return null;
    }

    public function getImageCodes()
    {
        return array_map(function ($imageConfig) {
            return $imageConfig['image'];
        }, $this->getImagesConfig());
    }

    public function getImagesConfig()
    {
        if (!isset($this->imagesConfig)) {
            $this->imagesConfig = $this->getConfig('images/config');
        }
        return $this->imagesConfig;
    }

    private function getConfig(string $path): array
    {
        $json = $this->scopeConfig->getValue("smartblinds_image_import/$path");
        try {
            $config = $this->json->unserialize($json);
            if (!is_array($config)) {
                throw new \InvalidArgumentException();
            }
        } catch (\InvalidArgumentException $e) {
            return [];
        }
        return array_values($config);
    }
}
