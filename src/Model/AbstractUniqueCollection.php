<?php
/** @noinspection PhpDocSignatureInspection */
declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

use webignition\BasilTranspiler\UnknownItemException;

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
            $this->add($item);
        }
    }

    abstract protected function add($item);

    /**
     * @throws UnknownItemException
     */
    public function get(string $id)
    {
        $item = $this->items[$id] ?? null;

        if (null === $item) {
            throw new UnknownItemException($id);
        }

        return $item;
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
            $new->add($item);
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

    protected function doAdd(UniqueItemInterface $item)
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
