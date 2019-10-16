<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\CompilationMetadata;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilTranspiler\Model\NamedDomIdentifierValue;
use webignition\BasilTranspiler\VariableNames;
use webignition\DomElementLocator\ElementLocator;
use webignition\WebDriverElementInspector\Inspector;

trait NamedDomIdentifierValueFunctionalDataProviderTrait
{
    public function namedDomIdentifierValueFunctionalDataProvider(): array
    {
        return [
            'element value, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        new DomIdentifier('input', 1)
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'expectedCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT',
                    ])),
                'expectedExecutedResult' => '',
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$inspector',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
            'element value, has parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('input', 1))
                            ->withParentIdentifier(new DomIdentifier('form[action="/action2"]'))
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'expectedCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT',
                    ])),
                'expectedExecutedResult' => 'test',
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$inspector',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
            'attribute value, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('input', 1))->withAttributeName('name')
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'expectedCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT',
                    ])),
                'expectedExecutedResult' => 'input-without-value',
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
            'attribute value, has parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('input', 1))
                            ->withAttributeName('name')
                            ->withParentIdentifier(new DomIdentifier('form[action="/action2"]'))
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'expectedCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT',
                    ])),
                'expectedExecutedResult' => 'input-2',
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
        ];
    }
}
