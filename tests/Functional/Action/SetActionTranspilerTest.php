<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilTranspiler\Action\SetActionTranspiler;
use webignition\BasilTranspiler\Tests\DataProvider\Action\SetActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;

class SetActionTranspilerTest extends AbstractTestCase
{
    use SetActionFunctionalDataProviderTrait;

    /**
     * @var SetActionTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = SetActionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider setActionFunctionalDataProvider
     */
    public function testTranspileForExecutableActions(
        ActionInterface $action,
        string $fixture,
        array $variableIdentifiers,
        array $additionalUseStatements,
        array $additionalSetupStatements,
        array $additionalTeardownStatements
    ) {
        $transpilableSource = $this->transpiler->transpile($action);

        $executableCall = $this->createExecutableCall(
            $transpilableSource,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $fixture,
            $additionalSetupStatements,
            $additionalTeardownStatements,
            $additionalUseStatements
        );

        eval($executableCall);
    }
}
