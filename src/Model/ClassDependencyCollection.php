<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

use webignition\BasilTranspiler\UnknownItemException;

class ClassDependencyCollection extends AbstractUniqueCollection implements \Iterator
{
    /**
     * @param string $id
     *
     * @return ClassDependency
     *
     * @throws UnknownItemException
     */
    public function get(string $id): ClassDependency
    {
        return parent::get($id);
    }

    /**
     * @return ClassDependency[]
     */
    public function getAll(): array
    {
        return parent::getAll();
    }

    public function withAdditionalItems(array $items): ClassDependencyCollection
    {
        return parent::withAdditionalItems($items);
    }

    public function merge(array $collections): ClassDependencyCollection
    {
        return parent::merge($collections);
    }

    protected function add($item)
    {
        if ($item instanceof ClassDependency) {
            $this->doAdd($item);
        }
    }

    // Iterator methods

    public function current(): ClassDependency
    {
        return parent::current();
    }
}
