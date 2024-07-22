<?php declare(strict_types=1);

namespace GoMage\BaseSuggestions\Model\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\UrlInterface;

class Suggestion extends AbstractSimpleObject
{
    private UrlInterface $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($data);
    }

    public function getLabel(): string
    {
        return $this->_get('label');
    }

    public function getUrl(): string
    {
        return $this->urlBuilder->getDirectUrl($this->_get('url'));
    }
}
