<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

abstract class AbstractUniqueCollection implements \Iterator
{
    /**
     * @var UniqueItemInterface[]
     */
    private $items = [];

    private $iteratorIndex = [];
    private $iteratorPosition = 0;

    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            if ($item instanceof UniqueItemInterface && $this->canBeAdded($item)) {
                $this->add($item);
            }
        }
    }

    abstract protected function canBeAdded($item): bool;

    public function get(string $id)
    {
        return $this->items[$id] ?? null;
    }

    public function getAll(): array
    {
        return array_values($this->items);
    }

    public function has(UniqueItemInterface $item): bool
    {
        return array_key_exists($item->getId(), $this->items);
    }

    public function withAdditionalItems(array $items)
    {
        $new = clone $this;

        foreach ($items as $item) {
            if ($this->canBeAdded($item)) {
                $new->add($item);
            }
        }

        return $new;
    }

    public function merge(array $collections)
    {
        $new = clone $this;

        foreach ($collections as $collection) {
            if ($collection instanceof AbstractUniqueCollection) {
                $new = $new->withAdditionalItems($collection->getAll());
            }
        }

        return $new;
    }

    protected function add(UniqueItemInterface $item)
    {
        $id = $item->getId();

        if (!array_key_exists($id, $this->items)) {
            $indexPosition = count($this->items);

            $this->items[$id] = $item;
            $this->iteratorIndex[$indexPosition] = $id;
        }
    }

    // Iterator methods

    public function rewind()
    {
        $this->iteratorPosition = 0;
    }

    public function current()
    {
        $key = $this->iteratorIndex[$this->iteratorPosition];

        return $this->items[$key];
    }

    public function key(): string
    {
        return $this->iteratorIndex[$this->iteratorPosition];
    }

    public function next()
    {
        ++$this->iteratorPosition;
    }

    public function valid(): bool
    {
        $key = $this->iteratorIndex[$this->iteratorPosition] ?? null;

        return $key !== null;
    }
}
