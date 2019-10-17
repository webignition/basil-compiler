<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Action;

use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilTranspiler\Action\ClickActionTranspiler;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ClickActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;

class ClickActionTranspilerTest extends AbstractTestCase
{
    use ClickActionFunctionalDataProviderTrait;

    /**
     * @var ClickActionTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = ClickActionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider clickActionFunctionalDataProvider
     */
    public function testTranspileForExecutableActions(
        ActionInterface $action,
        string $fixture,
        array $variableIdentifiers,
        array $additionalSetupStatements,
        array $additionalTeardownStatements,
        ?MetadataInterface $additionalCompilationMetadata = null
    ) {
        $source = $this->transpiler->transpile($action);

        $executableCall = $this->createExecutableCall(
            $source,
            $fixture,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $additionalSetupStatements,
            $additionalTeardownStatements,
            $additionalCompilationMetadata
        );

        eval($executableCall);
    }
}
