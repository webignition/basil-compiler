<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class VariablePlaceholderCollection extends AbstractUniqueCollection implements \Iterator
{
    /**
     * @return VariablePlaceholder[]
     */
    public function getAll(): array
    {
        return parent::getAll();
    }

    public function withAdditionalVariablePlaceholders(
        VariablePlaceholderCollection $collection
    ): VariablePlaceholderCollection {
        $new = clone $this;

        foreach ($collection as $variablePlaceholder) {
            $new->add($variablePlaceholder);
        }

        return $new;
    }

    protected function canBeAdded($item): bool
    {
        return $item instanceof VariablePlaceholder;
    }

    // Iterator methods

    public function current(): VariablePlaceholder
    {
        return parent::current();
    }
}
