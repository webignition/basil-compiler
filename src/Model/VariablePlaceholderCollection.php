<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class VariablePlaceholderCollection extends AbstractUniqueCollection implements \Iterator
{
    public function get(string $id): ?VariablePlaceholder
    {
        return parent::get($id);
    }

    /**
     * @return VariablePlaceholder[]
     */
    public function getAll(): array
    {
        return parent::getAll();
    }

    public function withAdditionalItems(array $items): VariablePlaceholderCollection
    {
        return parent::withAdditionalItems($items);
    }

    public function merge(array $collections): VariablePlaceholderCollection
    {
        return parent::merge($collections);
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
