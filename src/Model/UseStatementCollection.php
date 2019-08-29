<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

use webignition\BasilTranspiler\UnknownItemException;

class UseStatementCollection extends AbstractUniqueCollection implements \Iterator
{
    /**
     * @param string $id
     *
     * @return UseStatement
     *
     * @throws UnknownItemException
     */
    public function get(string $id): UseStatement
    {
        return parent::get($id);
    }

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

    protected function add($item)
    {
        if ($item instanceof UseStatement) {
            $this->doAdd($item);
        }
    }

    // Iterator methods

    public function current(): UseStatement
    {
        return parent::current();
    }
}
