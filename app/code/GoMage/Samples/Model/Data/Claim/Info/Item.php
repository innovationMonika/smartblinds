<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Data\Claim\Info;

use GoMage\Samples\Api\Data\Claim\Info\ItemInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class Item extends AbstractSimpleObject implements ItemInterface
{
    private const ID   = 'id';
    private const NAME = 'name';

    public function getId(): int
    {
        return $this->_get(self::ID);
    }

    public function setId(int $id)
    {
        return $this->setData(self::ID, $id);
    }

    public function getName(): string
    {
        return $this->_get(self::NAME);
    }

    public function setName(string $name)
    {
        return $this->setData(self::NAME, $name);
    }
}
