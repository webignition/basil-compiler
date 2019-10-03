<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class CompilationMetadata implements CompilationMetadataInterface
{
    private $classDependencies;
    private $variableDependencies;
    private $variableExports;

    public function __construct()
    {
        $this->classDependencies = new ClassDependencyCollection();
        $this->variableDependencies = new VariablePlaceholderCollection();
        $this->variableExports = new VariablePlaceholderCollection();
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

    public function withClassDependencies(ClassDependencyCollection $classDependencies): CompilationMetadataInterface
    {
        $new = clone $this;
        $new->classDependencies = $classDependencies;

        return $new;
    }

    public function withVariableDependencies(
        VariablePlaceholderCollection $variableDependencies
    ): CompilationMetadataInterface {
        $new = clone $this;
        $new->variableDependencies = $variableDependencies;

        return $new;
    }

    public function withVariableExports(VariablePlaceholderCollection $variableExports): CompilationMetadataInterface
    {
        $new = clone $this;
        $new->variableExports = $variableExports;

        return $new;
    }

    public function withAdditionalClassDependencies(
        ClassDependencyCollection $classDependencies
    ): CompilationMetadataInterface {
        $new = clone $this;
        $new->classDependencies = $new->classDependencies->merge([$classDependencies]);

        return $new;
    }

    public function withAdditionalVariableDependencies(
        VariablePlaceholderCollection $variableDependencies
    ): CompilationMetadataInterface {
        $new = clone $this;
        $new->variableDependencies = $new->variableDependencies->merge([$variableDependencies]);

        return $new;
    }


    public function withAdditionalVariableExports(
        VariablePlaceholderCollection $variableExports
    ): CompilationMetadataInterface {
        $new = clone $this;
        $new->variableExports = $new->variableExports->merge([$variableExports]);

        return $new;
    }

    /**
     * @param CompilationMetadataInterface[] $compilationMetadataCollection
     *
     * @return CompilationMetadataInterface
     */
    public function merge(array $compilationMetadataCollection): CompilationMetadataInterface
    {
        $classDependencies = new ClassDependencyCollection();
        $variableDependencies = new VariablePlaceholderCollection();
        $variableExports = new VariablePlaceholderCollection();

        foreach ($compilationMetadataCollection as $metadata) {
            $classDependencies = $classDependencies->merge([$metadata->getClassDependencies()]);
            $variableDependencies = $variableDependencies->merge([$metadata->getVariableDependencies()]);
            $variableExports = $variableExports->merge([$metadata->getVariableExports()]);
        }

        $new = clone $this;
        $new->classDependencies = $new->classDependencies->merge([$classDependencies]);
        $new->variableDependencies = $new->variableDependencies->merge([$variableDependencies]);
        $new->variableExports = $new->variableExports->merge([$variableExports]);

        return $new;
    }
}
