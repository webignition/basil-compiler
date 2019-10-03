<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilTranspiler\Action\SubmitActionTranspiler;
use webignition\BasilTranspiler\Model\CompilationMetadataInterface;
use webignition\BasilTranspiler\Tests\DataProvider\Action\SubmitActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;

class SubmitActionTranspilerTest extends AbstractTestCase
{
    use SubmitActionFunctionalDataProviderTrait;

    /**
     * @var SubmitActionTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = SubmitActionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider submitActionFunctionalDataProvider
     */
    public function testTranspileForExecutableActions(
        ActionInterface $action,
        string $fixture,
        array $variableIdentifiers,
        array $additionalSetupStatements,
        array $additionalTeardownStatements,
        ?CompilationMetadataInterface $additionalCompilationMetadata = null
    ) {
        $compilableSource = $this->transpiler->transpile($action);

        $executableCall = $this->createExecutableCall(
            $compilableSource,
            $fixture,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $additionalSetupStatements,
            $additionalTeardownStatements,
            $additionalCompilationMetadata
        );

        eval($executableCall);
    }
}
