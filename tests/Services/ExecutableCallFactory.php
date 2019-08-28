<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Services;

use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\UseStatementTranspiler;
use webignition\BasilTranspiler\VariableNameResolver;

class ExecutableCallFactory
{
    private $useStatementTranspiler;
    private $variableNameResolver;

    public function __construct(
        UseStatementTranspiler $useStatementTranspiler,
        VariableNameResolver $variableNameResolver
    ) {
        $this->useStatementTranspiler = $useStatementTranspiler;
        $this->variableNameResolver = $variableNameResolver;
    }

    public static function createFactory(): ExecutableCallFactory
    {
        return new ExecutableCallFactory(
            UseStatementTranspiler::createTranspiler(),
            new VariableNameResolver()
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
        $useStatements = $useStatements->withAdditionalUseStatements($additionalUseStatements);

        $executableCall = '';

        foreach ($useStatements as $key => $value) {
            $executableCall .= (string) $this->useStatementTranspiler->transpile($value) . ";\n";
        }

        foreach ($setupLines as $line) {
            $executableCall .= $line . "\n";
        }

        $transpilationResult = $transpilationResult->withContent(
            $this->variableNameResolver->resolve($transpilationResult->getContent(), $variableIdentifiers)
        );

        $executableCall .= 'return ' . (string) $transpilationResult . ';';

        return $executableCall;
    }
}
