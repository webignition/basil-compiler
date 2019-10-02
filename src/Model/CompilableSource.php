<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class CompilableSource implements CompilableSourceInterface
{
    private $statements;
    private $classDependencies;
    private $variableExports;
    private $variableDependencies;

    public function __construct(
        array $statements,
        ClassDependencyCollection $classDependencies,
        VariablePlaceholderCollection $variableExports,
        VariablePlaceholderCollection $variableDependencies
    ) {
        $this->statements = $statements;
        $this->classDependencies = $classDependencies;
        $this->variableExports = $variableExports;
        $this->variableDependencies = $variableDependencies;
    }

    /**
     * @return string[]
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    public function getClassDependencies(): ClassDependencyCollection
    {
        return $this->classDependencies;
    }

    public function getVariableExports(): VariablePlaceholderCollection
    {
        return $this->variableExports;
    }

    public function getVariableDependencies(): VariablePlaceholderCollection
    {
        return $this->variableDependencies;
    }

    public function withAdditionalStatements(array $statements): CompilableSourceInterface
    {
        $new = clone $this;
        $new->statements = array_merge($this->statements, $statements);

        return $new;
    }

    public function __toString(): string
    {
        return implode("\n", $this->statements);
    }
}
