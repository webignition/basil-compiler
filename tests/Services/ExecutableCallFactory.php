<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Services;

use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
use webignition\BasilTranspiler\ClassDependencyTranspiler;
use webignition\BasilTranspiler\VariablePlaceholderResolver;

class ExecutableCallFactory
{
    private $classDependencyTranspiler;
    private $variablePlaceholderResolver;

    public function __construct(
        ClassDependencyTranspiler $classDependencyTranspiler,
        VariablePlaceholderResolver $variablePlaceholderResolver
    ) {
        $this->classDependencyTranspiler = $classDependencyTranspiler;
        $this->variablePlaceholderResolver = $variablePlaceholderResolver;
    }

    public static function createFactory(): ExecutableCallFactory
    {
        return new ExecutableCallFactory(
            ClassDependencyTranspiler::createTranspiler(),
            new VariablePlaceholderResolver()
        );
    }

    public function create(
        CompilableSourceInterface $compilableSource,
        array $variableIdentifiers = [],
        array $setupStatements = [],
        array $teardownStatements = [],
        ?ClassDependencyCollection $additionalClassDependencies = null
    ): string {
        $additionalClassDependencies = $additionalClassDependencies ?? new ClassDependencyCollection();

        $classDependencies = $compilableSource->getClassDependencies();
        $classDependencies = $classDependencies->merge([
            $additionalClassDependencies,
        ]);

        $executableCall = '';

        foreach ($classDependencies as $key => $value) {
            $executableCall .= (string) $this->classDependencyTranspiler->transpile($value) . ";\n";
        }

        foreach ($setupStatements as $statement) {
            $executableCall .= $statement . "\n";
        }

        $statements = $compilableSource->getStatements();

        array_walk($statements, function (string &$statement) {
            $statement .= ';';
        });

        $content = $this->variablePlaceholderResolver->resolve(
            implode("\n", $statements),
            $variableIdentifiers
        );

        $executableCall .= $content;

        foreach ($teardownStatements as $statement) {
            $executableCall .= "\n";
            $executableCall .= $statement;
        }

        return $executableCall;
    }

    public function createWithReturn(
        CompilableSourceInterface $compilableSource,
        array $variableIdentifiers = [],
        array $setupStatements = [],
        array $teardownStatements = [],
        ?ClassDependencyCollection $additionalClassDependencies = null
    ): string {
        $statements = $compilableSource->getStatements();
        $lastStatementPosition = count($statements) - 1;
        $lastStatement = $statements[$lastStatementPosition];
        $lastStatement = 'return ' . $lastStatement;
        $statements[$lastStatementPosition] = $lastStatement;

        $compilableSourceWithReturn = new CompilableSource($statements);
        $compilableSourceWithReturn = $compilableSourceWithReturn->withClassDependencies(
            $compilableSource->getClassDependencies()
        );

        $compilableSourceWithReturn = $compilableSourceWithReturn->withVariableDependencies(
            $compilableSource->getVariableDependencies()
        );

        $compilableSourceWithReturn = $compilableSourceWithReturn->withVariableExports(
            $compilableSource->getVariableExports()
        );

        return $this->create(
            $compilableSourceWithReturn,
            $variableIdentifiers,
            $setupStatements,
            $teardownStatements,
            $additionalClassDependencies
        );
    }
}
