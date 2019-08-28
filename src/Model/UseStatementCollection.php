<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class UseStatementCollection implements \Iterator
{
    private $useStatements = [];

    private $iteratorIndex = [];
    private $iteratorPosition = 0;

    public function __construct(array $useStatements = [])
    {
        foreach ($useStatements as $useStatement) {
            if ($useStatement instanceof UseStatement) {
                $this->add($useStatement);
            }
        }
    }

    /**
     * @return UseStatement[]
     */
    public function getAll(): array
    {
        return array_values($this->useStatements);
    }

    public function withAdditionalUseStatements(UseStatementCollection $collection): UseStatementCollection
    {
        $new = clone $this;

        foreach ($collection as $useStatement) {
            $new->add($useStatement);
        }

        return $new;
    }

    private function add(UseStatement $useStatement)
    {
        $hash = $useStatement->getHash();

        if (!array_key_exists($hash, $this->useStatements)) {
            $indexPosition = count($this->useStatements);

            $this->useStatements[$hash] = $useStatement;
            $this->iteratorIndex[$indexPosition] = $hash;
        }
    }

    // Iterator methods

    public function rewind()
    {
        $this->iteratorPosition = 0;
    }

    public function current(): UseStatement
    {
        $useStatementKey = $this->iteratorIndex[$this->iteratorPosition];

        return $this->useStatements[$useStatementKey];
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
