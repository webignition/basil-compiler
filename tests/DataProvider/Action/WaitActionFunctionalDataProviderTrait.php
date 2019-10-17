<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilTranspiler\VariableNames;
use webignition\WebDriverElementInspector\Inspector;

trait WaitActionFunctionalDataProviderTrait
{
    public function waitActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        $emptyMetadata = new Metadata();

        $additionalMetadata = (new Metadata())
            ->withAdditionalClassDependencies(new ClassDependencyCollection([
                new ClassDependency(Inspector::class),
            ]));

        return [
            'wait action, literal duration' => [
                'action' => $actionFactory->createFromActionString('wait 10'),
                'fixture' => '/action-wait.html',
                'variableIdentifiers' => [
                    'DURATION' => '$duration',
                ],
                'additionalSetupStatements' => [
                    '$this->assertTrue(true);'
                ],
                'additionalTeardownStatements' => [],
                'additionalMetadata' => $emptyMetadata,
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
                'additionalSetupStatements' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalTeardownStatements' => [],
                'additionalMetadata' => $additionalMetadata,
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
                'additionalSetupStatements' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalTeardownStatements' => [],
                'additionalMetadata' => $additionalMetadata,
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
                'additionalSetupStatements' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalTeardownStatements' => [],
                'additionalMetadata' => $additionalMetadata,
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
                'additionalSetupStatements' => [],
                'additionalTeardownStatements' => [],
                'additionalMetadata' => $emptyMetadata,
                'expectedDuration' => 1200,
            ],
            'wait action, page property' => [
                'action' => $actionFactory->createFromActionString('wait $page.title'),
                'fixture' => '/action-wait.html',
                'variableIdentifiers' => [
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                    'DURATION' => '$duration',
                ],
                'additionalSetupStatements' => [],
                'additionalTeardownStatements' => [],
                'additionalMetadata' => $emptyMetadata,
                'expectedDuration' => 5,
            ],
            'wait action, environment value, value exists' => [
                'action' => $actionFactory->createFromActionString('wait $env.DURATION'),
                'fixture' => '/action-wait.html',
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                    'DURATION' => '$duration',
                ],
                'additionalSetupStatements' => [],
                'additionalTeardownStatements' => [],
                'additionalMetadata' => $emptyMetadata,
                'expectedDuration' => 5,
            ],
            'wait action, environment value, value does not exist' => [
                'action' => $actionFactory->createFromActionString('wait $env.NON_EXISTENT'),
                'fixture' => '/action-wait.html',
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                    'DURATION' => '$duration',
                ],
                'additionalSetupStatements' => [],
                'additionalTeardownStatements' => [],
                'additionalMetadata' => $emptyMetadata,
                'expectedDuration' => 0,
            ],
        ];
    }
}
