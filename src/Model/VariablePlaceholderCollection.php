<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

use webignition\BasilTranspiler\UnknownItemException;

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
            $this->doAdd($variablePlaceholder);
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->get($name);
    }

    /**
     * @param string $id
     *
     * @return VariablePlaceholder
     *
     * @throws UnknownItemException
     */
    public function get(string $id): VariablePlaceholder
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

    protected function add($item)
    {
        if ($item instanceof VariablePlaceholder) {
            $this->doAdd($item);
        }
    }

    // Iterator methods

    public function current(): VariablePlaceholder
    {
        return parent::current();
    }
}
