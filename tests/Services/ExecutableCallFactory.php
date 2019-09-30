<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Services;

use webignition\BasilTranspiler\Model\TranspilableSource;
use webignition\BasilTranspiler\Model\TranspilableSourceInterface;
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
        TranspilableSourceInterface $transpilableSource,
        array $variableIdentifiers = [],
        array $setupLines = [],
        array $teardownLines = [],
        ?UseStatementCollection $additionalUseStatements = null
    ): string {
        $additionalUseStatements = $additionalUseStatements ?? new UseStatementCollection();

        $useStatements = $transpilableSource->getUseStatements();
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

        $lines = $transpilableSource->getLines();

        array_walk($lines, function (string &$line) {
            $line .= ';';
        });

        $content = $this->variablePlaceholderResolver->resolve(
            implode("\n", $lines),
            $variableIdentifiers
        );

        $executableCall .= $content;

        foreach ($teardownLines as $line) {
            $executableCall .= "\n";
            $executableCall .= $line;
        }

        return $executableCall;
    }

    public function createWithReturn(
        TranspilableSourceInterface $transpilableSource,
        array $variableIdentifiers = [],
        array $setupLines = [],
        array $teardownLines = [],
        ?UseStatementCollection $additionalUseStatements = null
    ): string {
        $lines = $transpilableSource->getLines();
        $lastLinePosition = count($lines) - 1;
        $lastLine = $lines[$lastLinePosition];
        $lastLine = 'return ' . $lastLine;
        $lines[$lastLinePosition] = $lastLine;

        $transpilableSourceWithReturn = new TranspilableSource(
            $lines,
            $transpilableSource->getUseStatements(),
            $transpilableSource->getVariablePlaceholders()
        );

        return $this->create(
            $transpilableSourceWithReturn,
            $variableIdentifiers,
            $setupLines,
            $teardownLines,
            $additionalUseStatements
        );
    }
}
