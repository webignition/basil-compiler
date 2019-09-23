<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Action;

use PHPUnit\Framework\ExpectationFailedException;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;
use webignition\BasilTranspiler\Action\ActionTranspiler;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitForActionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\BasilTranspiler\VariableNames;

class ActionTranspilerTest extends AbstractTestCase
{
    use WaitActionFunctionalDataProviderTrait;
    use WaitForActionFunctionalDataProviderTrait;

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
        array $additionalPreLines,
        array $additionalPostLines,
        array $additionalUseStatements
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

        $this->expectNotToPerformAssertions();

        eval($executableCall);
    }

    public function transpileForExecutableActionsDataProvider()
    {
        return [
            'wait action' => current($this->waitActionFunctionalDataProvider()),
            'wait-for action' => current($this->waitForActionFunctionalDataProvider()),
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
                    new DomIdentifierValue(
                        new DomIdentifier(
                            new ElementExpression('.non-existent', ElementExpressionType::CSS_SELECTOR)
                        )
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
            'wait, attribute identifier examined value, element does not exist' => [
                'action' => new WaitAction(
                    'wait $elements.element_name.attribute_name',
                    new DomIdentifierValue(
                        (new DomIdentifier(
                            new ElementExpression('.non-existent', ElementExpressionType::CSS_SELECTOR)
                        ))->withAttributeName('attribute_name')
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
