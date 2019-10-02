<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class StatementCollection implements \Iterator, CompilableSourceInterface
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

    /**
     * @return string[]
     */
    public function getStatements(): array
    {
        $statementContent = [];

        foreach ($this->statements as $statement) {
            $statementContent[] = (string) $statement;
        }

        return $statementContent;
    }

    public function getClassDependencies(): ClassDependencyCollection
    {
        $classDependencies = new ClassDependencyCollection();

        foreach ($this->statements as $statement) {
            $classDependencies = $classDependencies->merge([
                $statement->getClassDependencies()
            ]);
        }

        return $classDependencies;
    }

    public function getVariableExports(): VariablePlaceholderCollection
    {
        $variableExports = new VariablePlaceholderCollection();

        foreach ($this->statements as $statement) {
            $variableExports = $variableExports->merge([
                $statement->getVariableExports(),
            ]);
        }

        return $variableExports;
    }

    public function getVariableDependencies(): VariablePlaceholderCollection
    {
        $variableDependencies = new VariablePlaceholderCollection();

        foreach ($this->statements as $statement) {
            $variableDependencies = $variableDependencies->merge([
                $statement->getVariableDependencies(),
            ]);
        }

        return $variableDependencies;
    }

    public function __toString(): string
    {
        return implode("\n", $this->getStatements());
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
