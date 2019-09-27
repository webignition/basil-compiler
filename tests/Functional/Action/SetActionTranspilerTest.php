<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilTranspiler\Action\SetActionTranspiler;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Tests\DataProvider\Action\SetActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\WebDriverElementInspector\Inspector;
use webignition\WebDriverElementMutator\Mutator;

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
        array $additionalPreLines,
        array $additionalPostLines
    ) {
        $transpilationResult = $this->transpiler->transpile($action);

        $executableCall = $this->createExecutableCall(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $fixture,
            array_merge(
                [
                    '$inspector = Inspector::create($crawler);',
                    '$mutator = Mutator::create();',
                ],
                $additionalPreLines
            ),
            $additionalPostLines,
            array_merge(
                [
                    new UseStatement(Inspector::class),
                    new UseStatement(Mutator::class),
                ],
                $additionalUseStatements
            )
        );

        eval($executableCall);
    }
}
