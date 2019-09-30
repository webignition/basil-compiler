<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilTranspiler\Action\WaitForActionTranspiler;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitForActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;

class WaitForActionTranspilerTest extends AbstractTestCase
{
    use WaitForActionFunctionalDataProviderTrait;

    /**
     * @var WaitForActionTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = WaitForActionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider waitForActionFunctionalDataProvider
     */
    public function testTranspileForExecutableActions(
        ActionInterface $action,
        string $fixture,
        array $variableIdentifiers,
        array $additionalUseStatements,
        array $additionalPreLines,
        array $additionalPostLines
    ) {
        $transpilableSource = $this->transpiler->transpile($action);

        $executableCall = $this->createExecutableCall(
            $transpilableSource,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $fixture,
            $additionalPreLines,
            $additionalPostLines,
            $additionalUseStatements
        );

        $executableCallLines = explode("\n", $executableCall);
        $waitForLine = array_pop($executableCallLines);

        $executableCallLines = array_merge($executableCallLines, [
            '$before = microtime(true);',
            $waitForLine,
            '$executionDurationInMilliseconds = (microtime(true) - $before) * 1000;',
            '$this->assertGreaterThan(100, $executionDurationInMilliseconds);',
        ]);

        $executableCall = implode("\n", $executableCallLines);

        eval($executableCall);
    }
}
