<?php declare(strict_types=1);

namespace Smartblinds\ConfigurableProduct\Plugin\ConfigurableProduct\Block\Product\View\Type\Configurable;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Serialize\Serializer\Json;
use Smartblinds\ConfigurableProduct\Model\AdditionalImages;

class AddAdditionalImages
{
    private Json $json;
    private AdditionalImages $additionalImages;

    private const ADDITIONAL_IMAGES_START_POSITION = 100;

    public function __construct(
        Json $json,
        AdditionalImages $additionalImages
    ) {
        $this->json = $json;
        $this->additionalImages = $additionalImages;
    }

    public function afterGetJsonConfig(
        Configurable $subject,
        string $result
    ) {
        $config = $this->json->unserialize($result);
        $imagesConfig = $config['images'] ?? [];
        $position = self::ADDITIONAL_IMAGES_START_POSITION;
        foreach ($this->additionalImages->getUrls() as $url) {
            foreach ($imagesConfig as $productId => &$images) {
                $imagesConfig[$productId][] = [
                    'thumb' => $url,
                    'img' => $url,
                    'full' => $url,
                    'caption' => 'additional image',
                    'position' => $position,
                    'isMain' => false,
                    'type' => 'image',
                    'videoUrl' => null
                ];
                $position++;
            }
        }
        $config['images'] = $imagesConfig;
        return $this->json->serialize($config);
    }
}
