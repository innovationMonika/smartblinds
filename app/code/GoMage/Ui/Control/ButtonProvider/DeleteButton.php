<?php declare(strict_types=1);

namespace GoMage\Ui\Control\ButtonProvider;

use GoMage\Ui\Control\ButtonProvider\UrlBuilder\UrlBuilder;
use Magento\Framework\App\RequestInterface;

class DeleteButton extends BaseButton
{
    protected $request;
    protected $urlBuilder;

    public function __construct(
        RequestInterface $request,
        UrlBuilder $urlBuilder,
        $data = []
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($data);
    }

    public function getButtonData()
    {
        if (!$this->request->getParam($this->data['requestFieldName'])) {
            return [];
        }
        $this->data['on_click'] = $this->getDeleteOnClick();
        return parent::getButtonData();
    }

    protected function getDeleteOnClick(): string
    {
        $confirmPhrase = __('Are you sure that you want to delete?');
        $urlPath = $this->getButtonParam('urlPath') ?: '';
        $urlParams = $this->getButtonParam('urlParams') ?: [];
        $url = $this->urlBuilder->getUrl($urlPath, $urlParams);
        return "deleteConfirm('{$confirmPhrase}', '{$url}')";
    }
}
