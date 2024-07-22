<?php declare(strict_types=1);

namespace GoMage\Ui\Control\ButtonProvider;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BaseButton implements ButtonProviderInterface
{
    protected $data;

    public function __construct($data = [])
    {
        $this->setButtonData($data);
    }

    protected function setButtonData(array $data)
    {
        $label = $data['label'] ?? '';
        $data['label'] = __($label);
        $this->data = $data;
    }

    public function getButtonData()
    {
        return $this->data;
    }

    public function getButtonParam(string $key)
    {
        return $this->data[$key] ?? null;
    }
}
