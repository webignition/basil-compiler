<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Services;

use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\UseStatementTranspiler;

class ExecutableCallFactory
{
    private $useStatementTranspiler;

    public function __construct(UseStatementTranspiler $useStatementTranspiler)
    {
        $this->useStatementTranspiler = $useStatementTranspiler;
    }

    public static function createFactory(): ExecutableCallFactory
    {
        return new ExecutableCallFactory(
            UseStatementTranspiler::createTranspiler()
        );
    }

    public function create(
        TranspilationResult $transpilationResult,
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

        $executableCall .= 'return ' . (string) $transpilationResult . ';';

        return $executableCall;
    }
}
