<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModel\Action\InputAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilTranspiler\VariableNames;

trait SetActionFunctionalDataProviderTrait
{
    private $setActionFunctionalVariableIdentifiers = [
        'ELEMENT_LOCATOR' => '$elementLocator',
        'ELEMENT' => '$element',
        'HAS' => '$has',
        'COLLECTION' => '$collection',
        'VALUE' => '$value',
        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => '$inspector',
        VariableNames::WEBDRIVER_ELEMENT_MUTATOR => '$mutator',
        'WEBDRIVER_DIMENSION' => '$webDriverDimension',
        VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
    ];

    public function setActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return array_merge(
            $this->setActionForTextInputFunctionalDataProvider(),
            $this->setActionForTextareaFunctionalDataProvider(),
            $this->setActionForSelectFunctionalDataProvider(),
            $this->setActionForOptionCollectionFunctionalDataProvider(),
            $this->setActionForRadioGroupFunctionalDataProvider(),
            [
                'input action, element identifier, element value' => [
                    'action' => new InputAction(
                        'set "input[name=input-without-value]" to $elements.textarea',
                        new DomIdentifier('input[name=input-without-value]'),
                        DomIdentifierValue::create('.textarea-non-empty'),
                        '"input[name=input-without-value]" to $elements.textarea'
                    ),
                    'fixture' => '/form.html',
                    'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                    'additionalUseStatements' => [],
                    'additionalPreLines' => [
                        '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                        '$this->assertEquals("", $input->getAttribute("value"));',
                    ],
                    'additionalPostLines' => [
                        '$this->assertEquals("textarea content", $input->getAttribute("value"));',
                    ],
                ],
                'input action, element identifier, attribute value' => [
                    'action' => new InputAction(
                        'set "input[name=input-without-value]" to $elements.form.action',
                        new DomIdentifier('input[name=input-without-value]'),
                        new DomIdentifierValue(
                            (new DomIdentifier('#form1'))->withAttributeName('action')
                        ),
                        '"input[name=input-without-value]" to $elements.form.action'
                    ),
                    'fixture' => '/form.html',
                    'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                    'additionalUseStatements' => [],
                    'additionalPreLines' => [
                        '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                        '$this->assertEquals("", $input->getAttribute("value"));',
                    ],
                    'additionalPostLines' => [
                        '$this->assertEquals("http://127.0.0.1:9080/action1", $input->getAttribute("value"));',
                    ],
                ],
                'input action, browser property' => [
                    'action' => $actionFactory->createFromActionString(
                        'set "input[name=input-without-value]" to $browser.size'
                    ),
                    'fixture' => '/form.html',
                    'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                    'additionalUseStatements' => [],
                    'additionalPreLines' => [
                        '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                        '$this->assertEquals("", $input->getAttribute("value"));',
                    ],
                    'additionalPostLines' => [
                        '$this->assertEquals("1200x1100", $input->getAttribute("value"));',
                    ],
                ],
                'input action, page property' => [
                    'action' => $actionFactory->createFromActionString(
                        'set "input[name=input-without-value]" to $page.url'
                    ),
                    'fixture' => '/form.html',
                    'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                    'additionalUseStatements' => [],
                    'additionalPreLines' => [
                        '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                        '$this->assertEquals("", $input->getAttribute("value"));',
                    ],
                    'additionalPostLines' => [
                        '$this->assertEquals("http://127.0.0.1:9080/form.html", $input->getAttribute("value"));',
                    ],
                ],
                'input action, environment value' => [
                    'action' => $actionFactory->createFromActionString(
                        'set "input[name=input-without-value]" to $env.TEST1'
                    ),
                    'fixture' => '/form.html',
                    'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                    'additionalUseStatements' => [],
                    'additionalPreLines' => [
                        '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                        '$this->assertEquals("", $input->getAttribute("value"));',
                    ],
                    'additionalPostLines' => [
                        '$this->assertEquals("environment value", $input->getAttribute("value"));',
                    ],
                ],
            ]
        );
    }

    private function setActionForTextInputFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'input action, literal value: empty text input, empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=input-without-value]" to ""'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                    '$this->assertEquals("", $input->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("", $input->getAttribute("value"));',
                ],
            ],
            'input action, literal value: empty text input, non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=input-without-value]" to "non-empty value"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                    '$this->assertEquals("", $input->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("non-empty value", $input->getAttribute("value"));',
                ],
            ],
            'input action, literal value: non-empty text input, empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=input-with-value]" to ""'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$input = $crawler->filter(\'input[name=input-with-value]\')->getElement(0);',
                    '$this->assertEquals("test", $input->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("", $input->getAttribute("value"));',
                ],
            ],
            'input action, literal value: non-empty text input, non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=input-with-value]" to "new value"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$input = $crawler->filter(\'input[name=input-with-value]\')->getElement(0);',
                    '$this->assertEquals("test", $input->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("new value", $input->getAttribute("value"));',
                ],
            ],
        ];
    }

    private function setActionForTextareaFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'input action, literal value: empty textarea, empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".textarea-empty" to ""'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$textarea = $crawler->filter(\'.textarea-empty\')->getElement(0);',
                    '$this->assertEquals("", $textarea->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("", $textarea->getAttribute("value"));',
                ],
            ],
            'input action, literal value: empty textarea, non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".textarea-empty" to "non-empty value"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$textarea = $crawler->filter(\'.textarea-empty\')->getElement(0);',
                    '$this->assertEquals("", $textarea->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("non-empty value", $textarea->getAttribute("value"));',
                ],
            ],
            'input action, literal value: non-empty textarea, empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".textarea-non-empty" to ""'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$textarea = $crawler->filter(\'.textarea-non-empty\')->getElement(0);',
                    '$this->assertEquals("textarea content", $textarea->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("", $textarea->getAttribute("value"));',
                ],
            ],
            'input action, literal value: non-empty textarea, non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".textarea-non-empty" to "new value"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$textarea = $crawler->filter(\'.textarea-non-empty\')->getElement(0);',
                    '$this->assertEquals("textarea content", $textarea->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("new value", $textarea->getAttribute("value"));',
                ],
            ],
        ];
    }

    private function setActionForSelectFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'input action, literal value: select none selected, empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".select-none-selected" to ""'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$select = $crawler->filter(\'.select-none-selected\')->getElement(0);',
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
            ],
            'input action, literal value: select none selected, invalid non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".select-none-selected" to "invalid"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$select = $crawler->filter(\'.select-none-selected\')->getElement(0);',
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
            ],
            'input action, literal value: select none selected, valid non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".select-none-selected" to "none-selected-2"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$select = $crawler->filter(\'.select-none-selected\')->getElement(0);',
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("none-selected-2", $select->getAttribute("value"));',
                ],
            ],
            'input action, literal value: select has selected, empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".select-has-selected" to ""'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$select = $crawler->filter(\'.select-has-selected\')->getElement(0);',
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
            ],
            'input action, literal value: select has selected, invalid non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".select-has-selected" to "invalid"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$select = $crawler->filter(\'.select-has-selected\')->getElement(0);',
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
            ],
            'input action, literal value: select has selected, valid non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".select-has-selected" to "has-selected-3"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$select = $crawler->filter(\'.select-has-selected\')->getElement(0);',
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("has-selected-3", $select->getAttribute("value"));',
                ],
            ],
        ];
    }

    private function setActionForOptionCollectionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'input action, literal value: option group none selected, empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".select-none-selected option" to ""'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$select = $crawler->filter(\'.select-none-selected\')->getElement(0);',
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
            ],
            'input action, literal value: option group none selected, invalid non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".select-none-selected option" to "invalid"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$select = $crawler->filter(\'.select-none-selected\')->getElement(0);',
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
            ],
            'input action, literal value: option group none selected, valid non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".select-none-selected option" to "none-selected-2"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$select = $crawler->filter(\'.select-none-selected\')->getElement(0);',
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("none-selected-2", $select->getAttribute("value"));',
                ],
            ],
            'input action, literal value: option group has selected, empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".select-has-selected option" to ""'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$select = $crawler->filter(\'.select-has-selected\')->getElement(0);',
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
            ],
            'input action, literal value: option group has selected, invalid non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".select-has-selected option" to "invalid"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$select = $crawler->filter(\'.select-has-selected\')->getElement(0);',
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
            ],
            'input action, literal value: option group has selected, valid non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".select-has-selected option" to "has-selected-3"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$select = $crawler->filter(\'.select-has-selected\')->getElement(0);',
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("has-selected-3", $select->getAttribute("value"));',
                ],
            ],
        ];
    }

    private function setActionForRadioGroupFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'input action, literal value: radio group none checked, empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=radio-not-checked]" to ""'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$radioGroup = $crawler->filter(\'input[name=radio-not-checked]\');',
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
                'additionalPostLines' => [
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
            ],
            'input action, literal value: radio group none checked, invalid non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=radio-not-checked]" to "invalid"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$radioGroup = $crawler->filter(\'input[name=radio-not-checked]\');',
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
                'additionalPostLines' => [
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
            ],
            'input action, literal value: radio group none checked, valid non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=radio-not-checked]" to "not-checked-2"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$radioGroup = $crawler->filter(\'input[name=radio-not-checked]\');',
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
                'additionalPostLines' => [
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
            ],
            'input action, literal value: radio group has checked, empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=radio-checked]" to ""'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$radioGroup = $crawler->filter(\'input[name=radio-checked]\');',
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
                'additionalPostLines' => [
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
            ],
            'input action, literal value: radio group has checked, invalid non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=radio-checked]" to "invalid"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$radioGroup = $crawler->filter(\'input[name=radio-checked]\');',
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
                'additionalPostLines' => [
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
            ],
            'input action, literal value: radio group has checked, valid non-empty value' => [
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=radio-checked]" to "checked-3"'
                ),
                'fixture' => '/form.html',
                'variableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$radioGroup = $crawler->filter(\'input[name=radio-checked]\');',
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
                'additionalPostLines' => [
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(1)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(2)->isSelected());',
                ],
            ],
        ];
    }
}
