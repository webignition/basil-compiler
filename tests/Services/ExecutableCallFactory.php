<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Services;

use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\UseStatementTranspiler;
use webignition\BasilTranspiler\VariablePlaceholderResolver;

class ExecutableCallFactory
{
    private $useStatementTranspiler;
    private $variablePlaceholderResolver;

    public function __construct(
        UseStatementTranspiler $useStatementTranspiler,
        VariablePlaceholderResolver $variablePlaceholderResolver
    ) {
        $this->useStatementTranspiler = $useStatementTranspiler;
        $this->variablePlaceholderResolver = $variablePlaceholderResolver;
    }

    public static function createFactory(): ExecutableCallFactory
    {
        return new ExecutableCallFactory(
            UseStatementTranspiler::createTranspiler(),
            new VariablePlaceholderResolver()
        );
    }

    public function create(
        CompilableSourceInterface $compilableSource,
        array $variableIdentifiers = [],
        array $setupStatements = [],
        array $teardownStatements = [],
        ?UseStatementCollection $additionalUseStatements = null
    ): string {
        $additionalUseStatements = $additionalUseStatements ?? new UseStatementCollection();

        $useStatements = $compilableSource->getUseStatements();
        $useStatements = $useStatements->merge([
            $additionalUseStatements,
        ]);

        $executableCall = '';

        foreach ($useStatements as $key => $value) {
            $executableCall .= (string) $this->useStatementTranspiler->transpile($value) . ";\n";
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
        ?UseStatementCollection $additionalUseStatements = null
    ): string {
        $statements = $compilableSource->getStatements();
        $lastStatementPosition = count($statements) - 1;
        $lastStatement = $statements[$lastStatementPosition];
        $lastStatement = 'return ' . $lastStatement;
        $statements[$lastStatementPosition] = $lastStatement;

        $transpilableSourceWithReturn = new CompilableSource(
            $statements,
            $compilableSource->getUseStatements(),
            $compilableSource->getVariablePlaceholders()
        );

        return $this->create(
            $transpilableSourceWithReturn,
            $variableIdentifiers,
            $setupStatements,
            $teardownStatements,
            $additionalUseStatements
        );
    }
}
