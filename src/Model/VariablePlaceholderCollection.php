<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class VariablePlaceholderCollection implements \Iterator
{
    private $variablePlaceholders = [];

    private $iteratorIndex = [];
    private $iteratorPosition = 0;

    public function __construct(array $variablePlaceholders = [])
    {
        foreach ($variablePlaceholders as $variablePlaceholder) {
            if ($variablePlaceholder instanceof VariablePlaceholder) {
                $this->add($variablePlaceholder);
            }
        }
    }

    /**
     * @return VariablePlaceholder[]
     */
    public function getAll(): array
    {
        return array_values($this->variablePlaceholders);
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

    private function add(VariablePlaceholder $variablePlaceholder)
    {
        $hash = $variablePlaceholder->getHash();

        if (!array_key_exists($hash, $this->variablePlaceholders)) {
            $indexPosition = count($this->variablePlaceholders);

            $this->variablePlaceholders[$hash] = $variablePlaceholder;
            $this->iteratorIndex[$indexPosition] = $hash;
        }
    }

    // Iterator methods

    public function rewind()
    {
        $this->iteratorPosition = 0;
    }

    public function current(): VariablePlaceholder
    {
        $useStatementKey = $this->iteratorIndex[$this->iteratorPosition];

        return $this->variablePlaceholders[$useStatementKey];
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
        $useStatementKey = $this->iteratorIndex[$this->iteratorPosition] ?? null;

        return $useStatementKey !== null;
    }
}
