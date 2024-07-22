<?php declare(strict_types=1);

namespace GoMage\Ui\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;

class DataProvider extends ModifierPoolDataProvider
{
    protected $collection;

    private $items;

    private PoolInterface $pool;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        $collectionFactory,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->collection = $collectionFactory->create();
        $this->pool = $pool ?: ObjectManager::getInstance()->get(PoolInterface::class);
    }

    public function getData()
    {
        if ($this->items) {
            return $this->items;
        }

        $this->items = [];
        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $this->items[$item->getData($this->primaryFieldName)] = $item->getData();
        }

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->items = $modifier->modifyData($this->items);
        }

        return $this->items;
    }
}
