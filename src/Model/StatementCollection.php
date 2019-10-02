<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class StatementCollection implements \Iterator
{
    /**
     * @var StatementInterface[]
     */
    private $statements = [];
    private $iteratorPosition = 0;

    public function __construct(array $statements = [])
    {
        foreach ($statements as $statement) {
            if ($statement instanceof StatementInterface) {
                $this->add($statement);
            }
        }
    }

    public function add(StatementInterface $statement)
    {
        $this->statements[] = $statement;
    }

    // Iterator methods

    public function rewind()
    {
        $this->iteratorPosition = 0;
    }

    public function current(): StatementInterface
    {
        return $this->statements[$this->iteratorPosition];
    }

    public function key(): int
    {
        return $this->iteratorPosition;
    }

    public function next()
    {
        ++$this->iteratorPosition;
    }

    public function valid(): bool
    {
        return isset($this->statements[$this->iteratorPosition]);
    }
}
