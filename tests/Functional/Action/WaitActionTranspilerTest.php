<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Action;

use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilTranspiler\Action\WaitActionTranspiler;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;

class WaitActionTranspilerTest extends AbstractTestCase
{
    use WaitActionFunctionalDataProviderTrait;

    /**
     * @var WaitActionTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = WaitActionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider waitActionFunctionalDataProvider
     */
    public function testTranspileForExecutableActions(
        ActionInterface $action,
        string $fixture,
        array $variableIdentifiers,
        array $additionalSetupStatements,
        array $additionalTeardownStatements,
        MetadataInterface $additionalCompilationMetadata,
        int $expectedDuration
    ) {
        $compilableSource = $this->transpiler->transpile($action);

        $expectedDurationThreshold = $expectedDuration + 1;

        $executableCall = $this->createExecutableCall(
            $compilableSource,
            $fixture,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $additionalSetupStatements,
            $additionalTeardownStatements,
            $additionalCompilationMetadata
        );

        $executableCallStatements = explode("\n", $executableCall);
        $sleepStatement = array_pop($executableCallStatements);

        $executableCallStatements = array_merge($executableCallStatements, [
            '$before = microtime(true);',
            $sleepStatement,
            '$executionDurationInMilliseconds = (microtime(true) - $before) * 1000;',
            '$this->assertGreaterThan(' . $expectedDuration . ', $executionDurationInMilliseconds);',
            '$this->assertLessThan(' . $expectedDurationThreshold . ', $executionDurationInMilliseconds);',
        ]);

        $executableCall = implode("\n", $executableCallStatements);

        eval($executableCall);
    }
}
