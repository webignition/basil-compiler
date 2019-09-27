<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Action;

use PHPUnit\Framework\ExpectationFailedException;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilTranspiler\Action\ActionTranspiler;
use webignition\BasilTranspiler\Tests\DataProvider\Action\BackActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ClickActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ForwardActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ReloadActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\SetActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\SubmitActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitForActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\BasilTranspiler\VariableNames;

class ActionTranspilerTest extends AbstractTestCase
{
    use WaitActionFunctionalDataProviderTrait;
    use WaitForActionFunctionalDataProviderTrait;
    use BackActionFunctionalDataProviderTrait;
    use ForwardActionFunctionalDataProviderTrait;
    use ReloadActionFunctionalDataProviderTrait;
    use ClickActionFunctionalDataProviderTrait;
    use SubmitActionFunctionalDataProviderTrait;
    use SetActionFunctionalDataProviderTrait;

    /**
     * @var ActionTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = ActionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider transpileForExecutableActionsDataProvider
     */
    public function testTranspileForExecutableActions(
        ActionInterface $action,
        string $fixture,
        array $variableIdentifiers,
        array $additionalUseStatements,
        array $additionalSetupLines,
        array $additionalTeardownLines
    ) {
        $transpilationResult = $this->transpiler->transpile($action);

        $executableCall = $this->createExecutableCall(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $fixture,
            $additionalSetupLines,
            $additionalTeardownLines,
            $additionalUseStatements
        );

//        echo $executableCall . "\n\n";

//        if (!in_array($action->getType(), [ActionTypes::CLICK, ActionTypes::SUBMIT])) {
//            $this->expectNotToPerformAssertions();
//        }

        eval($executableCall);
    }

    public function transpileForExecutableActionsDataProvider()
    {
        return [
            'wait action' => current($this->waitActionFunctionalDataProvider()),
            'wait-for action' => current($this->waitForActionFunctionalDataProvider()),
            'back action' => current($this->backActionFunctionalDataProvider()),
            'forward action' => current($this->forwardActionFunctionalDataProvider()),
            'reload action' => current($this->reloadActionFunctionalDataProvider()),
            'click action' => current($this->clickActionFunctionalDataProvider()),
            'submit action' => current($this->submitActionFunctionalDataProvider()),
            'set action' => current($this->setActionFunctionalDataProvider()),
        ];
    }

    /**
     * @dataProvider transpileForFailingActionsDataProvider
     */
    public function testTranspileForFailingActions(
        ActionInterface $action,
        array $variableIdentifiers,
        string $expectedExpectationFailedExceptionMessage
    ) {
        $transpilationResult = $this->transpiler->transpile($action);

        $executableCall = $this->createExecutableCall(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            '/action-wait.html'
        );

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($expectedExpectationFailedExceptionMessage);

        eval($executableCall);
    }

    public function transpileForFailingActionsDataProvider(): array
    {
        return [
            'wait action, element identifier examined value, element does not exist' => [
                'action' => new WaitAction(
                    'wait $elements.element_name',
                    new DomIdentifierValue(new DomIdentifier('.non-existent'))
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => '$webDriverElementInspector',
                    'DURATION' => '$duration',
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
            'wait, attribute identifier examined value, element does not exist' => [
                'action' => new WaitAction(
                    'wait $elements.element_name.attribute_name',
                    new DomIdentifierValue(
                        (new DomIdentifier('.non-existent'))->withAttributeName('attribute_name')
                    )
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => '$webDriverElementInspector',
                    'DURATION' => '$duration',
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
        ];
    }
}
