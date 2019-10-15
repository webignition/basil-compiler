<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Services;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadataInterface;
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
        ?CompilationMetadataInterface $additionalCompilationMetadata = null
    ): string {
        if (null !== $additionalCompilationMetadata) {
            $compilationMetadata = $compilableSource->getCompilationMetadata();
            $compilationMetadata = $compilationMetadata->merge([
                $compilationMetadata,
                $additionalCompilationMetadata
            ]);

            $compilableSource = $compilableSource->withCompilationMetadata($compilationMetadata);
        }

        $compilationMetadata = $compilableSource->getCompilationMetadata();
        $classDependencies = $compilationMetadata->getClassDependencies();

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
        ?CompilationMetadataInterface $additionalCompilationMetadata = null
    ): string {
        $statements = $compilableSource->getStatements();
        $lastStatementPosition = count($statements) - 1;
        $lastStatement = $statements[$lastStatementPosition];
        $lastStatement = 'return ' . $lastStatement;
        $statements[$lastStatementPosition] = $lastStatement;

        $compilableSourceWithReturn = (new CompilableSource())
            ->withStatements($statements)
            ->withCompilationMetadata($compilableSource->getCompilationMetadata());

        return $this->create(
            $compilableSourceWithReturn,
            $variableIdentifiers,
            $setupStatements,
            $teardownStatements,
            $additionalCompilationMetadata
        );
    }
}
