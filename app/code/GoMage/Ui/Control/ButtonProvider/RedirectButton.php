<?php

namespace GoMage\Ui\Control\ButtonProvider;

use GoMage\Ui\Control\ButtonProvider\UrlBuilder\UrlBuilder;

class RedirectButton extends BaseButton
{
    protected $request;
    protected $urlBuilder;

    public function __construct(
        UrlBuilder $urlBuilder,
        $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($data);
    }

    public function getButtonData()
    {
        $this->data['on_click'] = $this->getOnClick();
        return parent::getButtonData();
    }

    protected function getOnClick(): string
    {
        $urlPath = $this->getButtonParam('urlPath') ?: '';
        $urlParams = $this->getButtonParam('urlParams') ?: [];
        $url = $this->urlBuilder->getUrl($urlPath, $urlParams);
        return sprintf("location.href = '%s';", $url);
    }
}
