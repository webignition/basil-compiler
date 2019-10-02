<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

interface StatementInterface
{
    public function getContent(): string;
    public function getClassDependencies(): ClassDependencyCollection;
    public function getVariableDependencies(): VariablePlaceholderCollection;
    public function getVariableExports(): VariablePlaceholderCollection;

    public function withClassDependencies(ClassDependencyCollection $classDependencies): StatementInterface;
    public function withVariableDependencies(VariablePlaceholderCollection $variableDependencies): StatementInterface;
    public function withVariableExports(VariablePlaceholderCollection $variableExports): StatementInterface;
}
