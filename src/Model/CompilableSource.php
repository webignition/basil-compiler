<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class CompilableSource implements CompilableSourceInterface
{
    /**
     * @var string[]
     */
    private $statements;

    private $classDependencies;
    private $variableDependencies;
    private $variableExports;

    public function __construct(array $statements)
    {
        $this->statements = $statements;

        $this->classDependencies = new ClassDependencyCollection();
        $this->variableDependencies = new VariablePlaceholderCollection();
        $this->variableExports = new VariablePlaceholderCollection();
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

    /**
     * @param ClassDependencyCollection $classDependencies
     *
     * @return CompilableSourceInterface
     */
    public function withClassDependencies(ClassDependencyCollection $classDependencies): CompilableSourceInterface
    {
        $new = clone $this;
        $new->classDependencies = $classDependencies;

        return $new;
    }

    /**
     * @param VariablePlaceholderCollection $variableDependencies
     *
     * @return CompilableSourceInterface
     */
    public function withVariableDependencies(
        VariablePlaceholderCollection $variableDependencies
    ): CompilableSourceInterface {
        $new = clone $this;
        $new->variableDependencies = $variableDependencies;

        return $new;
    }

    /**
     * @param VariablePlaceholderCollection $variableExports
     *
     * @return CompilableSourceInterface
     */
    public function withVariableExports(VariablePlaceholderCollection $variableExports): CompilableSourceInterface
    {
        $new = clone $this;
        $new->variableExports = $variableExports;

        return $new;
    }

    public function __toString(): string
    {
        return implode("\n", $this->statements);
    }
}
