<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\VariableNames;
use webignition\WebDriverElementInspector\Inspector;

trait WaitActionFunctionalDataProviderTrait
{
    public function waitActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'wait action, literal duration' => [
                'action' => $actionFactory->createFromActionString('wait 10'),
                'fixture' => '/action-wait.html',
                'variableIdentifiers' => [
                    'DURATION' => '$duration',
                ],
                'additionalUseStatements' => [],
                'additionalSetupLines' => [
                    '$this->assertTrue(true);'
                ],
                'additionalTeardownLines' => [],
                'expectedDuration' => 10,
            ],
            'wait action, element value' => [
                'action' => new WaitAction(
                    'wait $elements.element_name',
                    new DomIdentifierValue(new DomIdentifier('[id="element-value"]'))
                ),
                'fixture' => '/action-wait.html',
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => '$webDriverElementInspector',
                    'DURATION' => '$duration',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalTeardownLines' => [],
                'expectedDuration' => 20,
            ],
            'wait action, attribute value, attribute exists' => [
                'action' => new WaitAction(
                    'wait $elements.element_name.attribute_name',
                    new DomIdentifierValue(
                        (new DomIdentifier('[id="attribute-value"]'))->withAttributeName('data-duration')
                    )
                ),
                'fixture' => '/action-wait.html',
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => '$webDriverElementInspector',
                    'DURATION' => '$duration',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalTeardownLines' => [],
                'expectedDuration' => 30,
            ],
            'wait action, attribute value, attribute does not exist' => [
                'action' => new WaitAction(
                    'wait $elements.element_name.attribute_name',
                    new DomIdentifierValue(
                        (new DomIdentifier('[id="attribute-value"]'))->withAttributeName('data-non-existent')
                    )
                ),
                'fixture' => '/action-wait.html',
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => '$webDriverElementInspector',
                    'DURATION' => '$duration',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalTeardownLines' => [],
                'expectedDuration' => 0,
            ],
            'wait action, browser property' => [
                'action' => $actionFactory->createFromActionString('wait $browser.size'),
                'fixture' => '/action-wait.html',
                'variableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                    'DURATION' => '$duration',
                ],
                'additionalUseStatements' => [],
                'additionalSetupLines' => [],
                'additionalTeardownLines' => [],
                'expectedDuration' => 1200,
            ],
            'wait action, page property' => [
                'action' => $actionFactory->createFromActionString('wait $page.title'),
                'fixture' => '/action-wait.html',
                'variableIdentifiers' => [
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                    'DURATION' => '$duration',
                ],
                'additionalUseStatements' => [],
                'additionalSetupLines' => [],
                'additionalTeardownLines' => [],
                'expectedDuration' => 5,
            ],
            'wait action, environment value, value exists' => [
                'action' => $actionFactory->createFromActionString('wait $env.DURATION'),
                'fixture' => '/action-wait.html',
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                    'DURATION' => '$duration',
                ],
                'additionalUseStatements' => [],
                'additionalSetupLines' => [],
                'additionalTeardownLines' => [],
                'expectedDuration' => 5,
            ],
            'wait action, environment value, value does not exist' => [
                'action' => $actionFactory->createFromActionString('wait $env.NON_EXISTENT'),
                'fixture' => '/action-wait.html',
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                    'DURATION' => '$duration',
                ],
                'additionalUseStatements' => [],
                'additionalSetupLines' => [],
                'additionalTeardownLines' => [],
                'expectedDuration' => 0,
            ],
        ];
    }
}
