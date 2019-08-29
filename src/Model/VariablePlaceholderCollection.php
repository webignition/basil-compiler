<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class VariablePlaceholderCollection extends AbstractUniqueCollection implements \Iterator
{
    public static function createCollection(array $names): VariablePlaceholderCollection
    {
        $collection = new VariablePlaceholderCollection();

        foreach ($names as $name) {
            if (is_string($name)) {
                $collection->create($name);
            }
        }

        return $collection;
    }

    public function create(string $name): VariablePlaceholder
    {
        $variablePlaceholder = new VariablePlaceholder($name);

        if (!$this->has($variablePlaceholder)) {
            $this->add($variablePlaceholder);
        }

        return $this->get($name);
    }

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
