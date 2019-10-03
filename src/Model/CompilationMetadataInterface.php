<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

interface CompilationMetadataInterface
{
    public function getClassDependencies(): ClassDependencyCollection;
    public function getVariableExports(): VariablePlaceholderCollection;
    public function getVariableDependencies(): VariablePlaceholderCollection;

    public function withClassDependencies(ClassDependencyCollection $classDependencies): CompilationMetadataInterface;
    public function withVariableDependencies(
        VariablePlaceholderCollection $variableDependencies
    ): CompilationMetadataInterface;
    public function withVariableExports(VariablePlaceholderCollection $variableExports): CompilationMetadataInterface;

    public function withAdditionalClassDependencies(
        ClassDependencyCollection $classDependencies
    ): CompilationMetadataInterface;

    public function withAdditionalVariableDependencies(
        VariablePlaceholderCollection $variableDependencies
    ): CompilationMetadataInterface;

    public function withAdditionalVariableExports(
        VariablePlaceholderCollection $variableExports
    ): CompilationMetadataInterface;

    /**
     * @param CompilationMetadataInterface[] $compilationMetadataCollection
     *
     * @return CompilationMetadataInterface
     */
    public function merge(array $compilationMetadataCollection): CompilationMetadataInterface;
}
