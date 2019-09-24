<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilTranspiler\Action\BrowserOperationActionTranspiler;
use webignition\BasilTranspiler\Tests\DataProvider\Action\BackActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ForwardActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ReloadActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;

class BrowserOperationActionTranspilerTest extends AbstractTestCase
{
    use BackActionFunctionalDataProviderTrait;
    use ForwardActionFunctionalDataProviderTrait;
    use ReloadActionFunctionalDataProviderTrait;

    /**
     * @var BrowserOperationActionTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = BrowserOperationActionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider backActionFunctionalDataProvider
     * @dataProvider forwardActionFunctionalDataProvider
     * @dataProvider reloadActionFunctionalDataProvider
     */
    public function testTranspileForExecutableActions(
        ActionInterface $action,
        string $fixture,
        array $variableIdentifiers,
        array $additionalUseStatements,
        array $additionalPreLines,
        array $additionalPostLines
    ) {
        $transpilationResult = $this->transpiler->transpile($action);

        $executableCall = $this->createExecutableCall(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $fixture,
            $additionalPreLines,
            $additionalPostLines,
            $additionalUseStatements
        );

        eval($executableCall);
    }
}