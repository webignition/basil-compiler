<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class Statement implements StatementInterface, CompilableSourceInterface
{
    private $content;
    private $classDependencies;
    private $variableDependencies;
    private $variableExports;

    public function __construct(string $content)
    {
        $this->content = $content;

        $this->classDependencies = new ClassDependencyCollection();
        $this->variableDependencies = new VariablePlaceholderCollection();
        $this->variableExports = new VariablePlaceholderCollection();
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getClassDependencies(): ClassDependencyCollection
    {
        return $this->classDependencies;
    }

    public function getVariableDependencies(): VariablePlaceholderCollection
    {
        return $this->variableDependencies;
    }

    public function getVariableExports(): VariablePlaceholderCollection
    {
        return $this->variableExports;
    }

    public function withClassDependencies(ClassDependencyCollection $classDependencies): StatementInterface
    {
        $new = clone $this;
        $new->classDependencies = $classDependencies;

        return $new;
    }

    /**
     * @param VariablePlaceholderCollection $variableDependencies
     *
     * @return StatementInterface|CompilableSourceInterface
     */
    public function withVariableDependencies(VariablePlaceholderCollection $variableDependencies): StatementInterface
    {
        $new = clone $this;
        $new->variableDependencies = $variableDependencies;

        return $new;
    }

    public function withVariableExports(VariablePlaceholderCollection $variableExports): StatementInterface
    {
        $new = clone $this;
        $new->variableExports = $variableExports;

        return $new;
    }

    /**
     * @return string[]
     */
    public function getStatements(): array
    {
        return [
            $this->content,
        ];
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
