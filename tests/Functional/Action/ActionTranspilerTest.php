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
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilTranspiler\Action\ActionTranspiler;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\BasilTranspiler\Tests\Services\ExecutableCallFactory;
use webignition\BasilTranspiler\VariableNames;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementInspector\Inspector;

class ActionTranspilerTest extends AbstractTestCase
{
    const DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME = '$domCrawlerNavigator';
    const PHPUNIT_TEST_CASE_VARIABLE_NAME = '$this';

    const VARIABLE_IDENTIFIERS = [
        VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
        VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
    ];

    /**
     * @var ActionTranspiler
     */
    private $transpiler;

    /**
     * @var ExecutableCallFactory
     */
    private $executableCallFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = ActionTranspiler::createTranspiler();
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }

    /**
     * @dataProvider waitActionsDataProvider
     */
    public function testTranspileForExecutableWaitActions(
        ActionInterface $action,
        int $expectedDuration,
        array $variableIdentifiers,
        array $additionalPreLines = [],
        array $additionalUseStatements = []
    ) {
        $transpilationResult = $this->transpiler->transpile($action);

        $expectedDurationThreshold = $expectedDuration + 1;

        $executableCall = $this->createExecutableCall(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            '/action-wait.html',
            $additionalPreLines,
            [],
            $additionalUseStatements
        );

        $executableCallLines = explode("\n", $executableCall);
        $sleepLine = array_pop($executableCallLines);

        $executableCallLines = array_merge($executableCallLines, [
            '$before = microtime(true);',
            $sleepLine,
            '$executionDurationInMilliseconds = (microtime(true) - $before) * 1000;',
            '$this->assertGreaterThan(' . $expectedDuration . ', $executionDurationInMilliseconds);',
            '$this->assertLessThan(' . $expectedDurationThreshold . ', $executionDurationInMilliseconds);',
        ]);

        $executableCall = implode("\n", $executableCallLines);

        eval($executableCall);
    }

    public function waitActionsDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'wait action, literal duration' => [
                'action' => $actionFactory->createFromActionString('wait 10'),
                'expectedDuration' => 10,
                'variableIdentifiers' => [
                    'DURATION' => '$duration',
                ],
            ],
            'wait action, element value' => [
                'action' => new WaitAction(
                    'wait $elements.element_name',
                    new DomIdentifierValue(
                        new DomIdentifier(
                            new ElementExpression('[id="element-value"]', ElementExpressionType::CSS_SELECTOR)
                        )
                    )
                ),
                'expectedDuration' => 20,
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => '$webDriverElementInspector',
                    'DURATION' => '$duration',
                ],
                'additionalPreLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'wait action, attribute value, attribute exists' => [
                'action' => new WaitAction(
                    'wait $elements.element_name.attribute_name',
                    new DomIdentifierValue(
                        (new DomIdentifier(
                            new ElementExpression('[id="attribute-value"]', ElementExpressionType::CSS_SELECTOR)
                        ))->withAttributeName('data-duration')
                    )
                ),
                'expectedDuration' => 30,
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => '$webDriverElementInspector',
                    'DURATION' => '$duration',
                ],
                'additionalPreLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'wait action, attribute value, attribute does not exist' => [
                'action' => new WaitAction(
                    'wait $elements.element_name.attribute_name',
                    new DomIdentifierValue(
                        (new DomIdentifier(
                            new ElementExpression('[id="attribute-value"]', ElementExpressionType::CSS_SELECTOR)
                        ))->withAttributeName('data-non-existent')
                    )
                ),
                'expectedDuration' => 0,
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => '$webDriverElementInspector',
                    'DURATION' => '$duration',
                ],
                'additionalPreLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'wait action, browser property' => [
                'action' => $actionFactory->createFromActionString('wait $browser.size'),
                'expectedDuration' => 1200,
                'variableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                    'DURATION' => '$duration',
                ],
            ],
            'wait action, page property' => [
                'action' => $actionFactory->createFromActionString('wait $page.title'),
                'expectedDuration' => 5,
                'variableIdentifiers' => [
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                    'DURATION' => '$duration',
                ],
            ],
            'wait action, environment value, value exists' => [
                'action' => $actionFactory->createFromActionString('wait $env.DURATION'),
                'expectedDuration' => 5,
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                    'DURATION' => '$duration',
                ],
            ],
            'wait action, environment value, value does not exist' => [
                'action' => $actionFactory->createFromActionString('wait $env.NON_EXISTENT'),
                'expectedDuration' => 0,
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                    'DURATION' => '$duration',
                ],
            ],
        ];
    }

    /**
     * @dataProvider waitForActionsDataProvider
     */
    public function testTranspileForExecutableWaitForActions(
        ActionInterface $action,
        array $variableIdentifiers,
        array $additionalPreLines = [],
        array $additionalUseStatements = []
    ) {
        $transpilationResult = $this->transpiler->transpile($action);

        $executableCall = $this->createExecutableCall(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            '/action-wait-for.html',
            $additionalPreLines,
            [],
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

    public function waitForActionsDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'wait-for action, css selector' => [
                'action' => $actionFactory->createFromActionString('wait-for "#hello"'),
                'variableIdentifiers' => [
                    VariableNames::PANTHER_CRAWLER => '$crawler',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                ],
            ],
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

    private function createExecutableCall(
        TranspilationResultInterface $transpilationResult,
        array $variableIdentifiers,
        string $fixture,
        array $additionalPreLines = [],
        array $additionalPostLines = [],
        array $additionalUseStatements = []
    ): string {
        return $this->executableCallFactory->create(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            array_merge(
                [
                    '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                    '$domCrawlerNavigator = Navigator::create($crawler); ',
                ],
                $additionalPreLines
            ),
            $additionalPostLines,
            new UseStatementCollection(array_merge(
                [
                    new UseStatement(Navigator::class),
                ],
                $additionalUseStatements
            ))
        );
    }
}
