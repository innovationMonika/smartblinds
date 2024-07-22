<?php

namespace GoMage\Catalog\Plugin\Block\Product\View;

use Magento\Framework\Serialize\Serializer\Json;

class GalleryOptions
{
    private Json $jsonSerializer;

    public function __construct(
        Json $jsonSerializer
    ) {
        $this->jsonSerializer = $jsonSerializer;
    }

    public function afterGetOptionsJson(
        \Magento\Catalog\Block\Product\View\GalleryOptions $subject,
        string $result
    ) {
        $resultDecoded = $this->jsonSerializer->unserialize($result);
        $resultDecoded['maxheight'] = $subject->getVar('gallery_max_thumbs_height', 'Magento_ConfigurableProduct');
        return $this->jsonSerializer->serialize($resultDecoded);
    }
}
