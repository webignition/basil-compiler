<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class UseStatementCollection extends AbstractUniqueCollection implements \Iterator
{
    /**
     * @return UseStatement[]
     */
    public function getAll(): array
    {
        return parent::getAll();
    }

    public function withAdditionalItems(array $items): UseStatementCollection
    {
        return parent::withAdditionalItems($items);
    }

    public function merge(array $collections): UseStatementCollection
    {
        return parent::merge($collections);
    }

    protected function canBeAdded($item): bool
    {
        return $item instanceof UseStatement;
    }

    // Iterator methods

    public function current(): UseStatement
    {
        return parent::current();
    }
}
