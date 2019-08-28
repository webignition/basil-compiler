<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

abstract class AbstractUniqueCollection implements \Iterator
{
    private $items = [];

    private $iteratorIndex = [];
    private $iteratorPosition = 0;

    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            if ($this->canBeAdded($item)) {
                $this->add($item);
            }
        }
    }

    abstract protected function canBeAdded($item): bool;

    public function getAll(): array
    {
        return array_values($this->items);
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

    protected function add($item)
    {
        $hash = md5((string) $item);

        if (!array_key_exists($hash, $this->items)) {
            $indexPosition = count($this->items);

            $this->items[$hash] = $item;
            $this->iteratorIndex[$indexPosition] = $hash;
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
