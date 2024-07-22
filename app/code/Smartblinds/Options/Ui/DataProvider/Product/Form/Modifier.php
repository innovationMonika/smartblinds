<?php declare(strict_types=1);

namespace Smartblinds\Options\Ui\DataProvider\Product\Form;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class Modifier extends AbstractModifier implements ModifierInterface
{
    protected $pool;
    protected $meta = [];

    public function __construct(
        PoolInterface $pool
    ) {
        $this->pool = $pool;
    }

    public function modifyData(array $data)
    {
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $data = $modifier->modifyData($data);
        }

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->meta = $modifier->modifyMeta($this->meta);
        }

        return $this->meta;
    }
}
