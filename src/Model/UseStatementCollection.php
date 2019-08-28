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

    public function withAdditionalUseStatements(UseStatementCollection $collection): UseStatementCollection
    {
        $new = clone $this;

        foreach ($collection as $useStatement) {
            $new->add($useStatement);
        }

        return $new;
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
