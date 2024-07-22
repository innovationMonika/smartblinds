<?php declare(strict_types=1);

namespace GoMage\Ui\Model;

class EntityRegistry
{
    private $entites;

    public function get(string $key)
    {
        return $this->entites[$key] ?? null;
    }

    public function set(string $key, $entity)
    {
        $this->entites[$key] = $entity;
    }
}
