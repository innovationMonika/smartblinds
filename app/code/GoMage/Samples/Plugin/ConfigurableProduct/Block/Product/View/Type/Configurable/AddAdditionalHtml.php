<?php declare(strict_types=1);

namespace GoMage\Samples\Plugin\ConfigurableProduct\Block\Product\View\Type\Configurable;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;

class AddAdditionalHtml
{
    private Json $json;

    /**
     * @var UrlInterface
     */
    protected $url;

    public function __construct(
        UrlInterface $url,
        Json $json,
    ) {
        $this->url = $url;
        $this->json = $json;
    }

    public function afterGetJsonConfig(
        Configurable $subject,
        string $result
    ) {
        $config = $this->json->unserialize($result);
        $config['swatchesAdditionalHtml']['swatchAttribute']['color'] = $this->getSamplesHtml();
        return $this->json->serialize($config);
    }


    private function getSamplesHtml(): string
    {
        $freeSampleText = __('Request free colour sample');
        $url = $this->url->getUrl('samples');

        return <<<HTML
            <div class="sample-block sample-block-in-swatches">
                <div class="sample-block-inner">
                    <div class="sample-block-content">
                        <div class="sample-state"></div>
                        <div class="sample-add" onclick="location.href='$url'">$freeSampleText</div>
                    </div>
                </div>
            </div>
        HTML;
    }
}
