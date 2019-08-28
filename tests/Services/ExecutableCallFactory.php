<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Services;

use webignition\BasilTranspiler\Model\TranspilationResult;
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
        TranspilationResult $transpilationResult,
        array $variableIdentifiers = [],
        array $setupLines = [],
        ?UseStatementCollection $additionalUseStatements = null
    ): string {
        $additionalUseStatements = $additionalUseStatements ?? new UseStatementCollection();

        $useStatements = $transpilationResult->getUseStatements();
        $useStatements = $useStatements->merge([
            $additionalUseStatements,
        ]);

        $executableCall = '';

        foreach ($useStatements as $key => $value) {
            $executableCall .= (string) $this->useStatementTranspiler->transpile($value) . ";\n";
        }

        foreach ($setupLines as $line) {
            $executableCall .= $line . "\n";
        }

        $lines = $transpilationResult->getLines();

        array_walk($lines, function (string &$line) {
            $line .= ';';
        });

        $content = $this->variablePlaceholderResolver->resolve(
            implode("\n", $lines),
            $variableIdentifiers
        );

        $executableCall .= $content;

        return $executableCall;
    }

    public function createWithReturn(
        TranspilationResult $transpilationResult,
        array $variableIdentifiers = [],
        array $setupLines = [],
        ?UseStatementCollection $additionalUseStatements = null
    ): string {
        $lines = $transpilationResult->getLines();
        $lastLinePosition = count($lines) - 1;
        $lastLine = $lines[$lastLinePosition];
        $lastLine = 'return ' . $lastLine;
        $lines[$lastLinePosition] = $lastLine;

        $transpilationResultWithReturn = new TranspilationResult(
            $lines,
            $transpilationResult->getUseStatements(),
            $transpilationResult->getVariablePlaceholders()
        );

        return $this->create(
            $transpilationResultWithReturn,
            $variableIdentifiers,
            $setupLines,
            $additionalUseStatements
        );
    }
}
