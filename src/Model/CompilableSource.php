<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class CompilableSource implements CompilableSourceInterface
{
    private $statements;
    private $classDependencies;
    private $variablePlaceholders;
    private $variableDependencies;

    public function __construct(
        array $statements,
        ClassDependencyCollection $classDependencies,
        VariablePlaceholderCollection $variablePlaceholders,
        VariablePlaceholderCollection $variableDependencies
    ) {
        $this->statements = $statements;
        $this->classDependencies = $classDependencies;
        $this->variablePlaceholders = $variablePlaceholders;
        $this->variableDependencies = $variableDependencies;
    }

    public function extend(
        string $template,
        ClassDependencyCollection $classDependencies,
        VariablePlaceholderCollection $variablePlaceholders,
        VariablePlaceholderCollection $variableDependencies
    ): CompilableSourceInterface {
        return new CompilableSource(
            explode("\n", sprintf($template, (string) $this)),
            $this->getClassDependencies()->merge([$classDependencies]),
            $this->getVariablePlaceholders()->merge([$variablePlaceholders]),
            $this->getVariableDependencies()->merge([$variableDependencies])
        );
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

    public function getVariablePlaceholders(): VariablePlaceholderCollection
    {
        return $this->variablePlaceholders;
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
