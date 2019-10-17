<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Services;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\MetadataInterface;
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
        SourceInterface $source,
        array $variableIdentifiers = [],
        array $setupStatements = [],
        array $teardownStatements = [],
        ?MetadataInterface $additionalMetadata = null
    ): string {
        if (null !== $additionalMetadata) {
            $metadata = $source->getMetadata();
            $metadata = $metadata->merge([
                $metadata,
                $additionalMetadata
            ]);

            $source = $source->withMetadata($metadata);
        }

        $metadata = $source->getMetadata();
        $classDependencies = $metadata->getClassDependencies();

        $executableCall = '';

        foreach ($classDependencies as $key => $value) {
            $executableCall .= (string) $this->classDependencyTranspiler->transpile($value) . ";\n";
        }

        foreach ($setupStatements as $statement) {
            $executableCall .= $statement . "\n";
        }

        $statements = $source->getStatements();

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
        SourceInterface $source,
        array $variableIdentifiers = [],
        array $setupStatements = [],
        array $teardownStatements = [],
        ?MetadataInterface $additionalMetadata = null
    ): string {
        $statements = $source->getStatements();
        $lastStatementPosition = count($statements) - 1;
        $lastStatement = $statements[$lastStatementPosition];
        $lastStatement = 'return ' . $lastStatement;
        $statements[$lastStatementPosition] = $lastStatement;

        $sourceWithReturn = (new Source())
            ->withStatements($statements)
            ->withMetadata($source->getMetadata());

        return $this->create(
            $sourceWithReturn,
            $variableIdentifiers,
            $setupStatements,
            $teardownStatements,
            $additionalMetadata
        );
    }
}
