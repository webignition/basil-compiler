<?php
/** @noinspection DuplicatedCode */
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Assertion;

use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\Assertion\AssertionTranspiler;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\ExistsAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IsAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\NotExistsAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\UnhandledAssertionDataProviderTrait;
use webignition\BasilTranspiler\VariableNames;
use webignition\SymfonyDomCrawlerNavigator\Model\ElementLocator;
use webignition\SymfonyDomCrawlerNavigator\Model\LocatorType;

class AssertionTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use ExistsAssertionDataProviderTrait;
    use IsAssertionDataProviderTrait;
    use NotExistsAssertionDataProviderTrait;
    use UnhandledAssertionDataProviderTrait;

    /**
     * @var AssertionTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = AssertionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider existsAssertionDataProvider
     * @dataProvider isAssertionDataProvider
     * @dataProvider notExistsAssertionDataProvider
     */
    public function testHandlesDoesHandle(AssertionInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider handlesDoesNotHandleDataProvider
     * @dataProvider unhandledAssertionDataProvider
     */
    public function testHandlesDoesNotHandle(object $model)
    {
        $this->assertFalse($this->transpiler->handles($model));
    }

    public function handlesDoesNotHandleDataProvider(): array
    {
        return [
            'non-value object' => [
                'value' => new \stdClass(),
            ],
        ];
    }

    /**
     * @dataProvider transpileDataProvider
     */
    public function testTranspile(
        AssertionInterface $assertion,
        string $expectedContentPattern,
        UseStatementCollection $expectedUseStatements,
        VariablePlaceholderCollection $expectedPlaceholders
    ) {
        $transpilationResult = $this->transpiler->transpile($assertion);

        $this->assertRegExp($expectedContentPattern, (string) $transpilationResult);
        $this->assertEquals($expectedUseStatements->getAll(), $transpilationResult->getUseStatements()->getAll());
        $this->assertEquals($expectedPlaceholders->getAll(), $transpilationResult->getVariablePlaceholders()->getAll());
    }

    public function transpileDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        $phpUnitTestCasePlaceholder = new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE);
        $domCrawlerNavigatorPlaceholder = new VariablePlaceholder(VariableNames::DOM_CRAWLER_NAVIGATOR);
        $environmentVariablePlaceholder = new VariablePlaceholder('ENVIRONMENT_VARIABLE');
        $environmentVariableArrayPlaceholder = new VariablePlaceholder(VariableNames::ENVIRONMENT_VARIABLE_ARRAY);
        $pantherClientPlaceholder = new VariablePlaceholder(VariableNames::PANTHER_CLIENT);
        $browserVariablePlaceholder = new VariablePlaceholder('BROWSER_VARIABLE');
        $pageVariablePlaceholder = new VariablePlaceholder('PAGE_VARIABLE');
        $elementLocatorPlaceholder = new VariablePlaceholder('ELEMENT_LOCATOR');
        $elementPlaceholder = new VariablePlaceholder('ELEMENT');
        $hasPlaceholder = new VariablePlaceholder('HAS');
        $attributePlaceholder = new VariablePlaceholder('ATTRIBUTE');
        $webDriverDimensionPlaceholder = new VariablePlaceholder('WEBDRIVER_DIMENSION');

        return [
            'exists comparison, element identifier examined value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" exists'
                ),
                'expectedContentPattern' =>
                    '/^'
                    . $hasPlaceholder . ' = '. $domCrawlerNavigatorPlaceholder . '->has\(.*\)' . "\n"
                    . $phpUnitTestCasePlaceholder
                    . '->assertTrue\(' . $hasPlaceholder . '\)$/',
                'expectedUseStatements' => new UseStatementCollection([
                    new UseStatement(ElementLocator::class),
                    new UseStatement(LocatorType::class),
                ]),
                'expectedVariablePlaceholders' => new VariablePlaceholderCollection([
                    $phpUnitTestCasePlaceholder,
                    $domCrawlerNavigatorPlaceholder,
                    $hasPlaceholder,
                ]),
            ],
            'exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name exists'
                ),
                'expectedContentPattern' => '/^'
                    . $elementLocatorPlaceholder . '.+' . "\n"
                    . $hasPlaceholder . ' = '. $domCrawlerNavigatorPlaceholder . '->hasOne\(.*\)' . "\n"
                    . $phpUnitTestCasePlaceholder . '->assertTrue\(' . $hasPlaceholder . '\)' . "\n"
                    . $elementPlaceholder . ' = '
                    . $domCrawlerNavigatorPlaceholder . '->findOne\(' . $elementLocatorPlaceholder . '\)' . "\n"
                    . $attributePlaceholder . ' = '
                    . $elementPlaceholder . '->getAttribute\(\'attribute_name\'\)' . "\n"
                    . $phpUnitTestCasePlaceholder . '->assertNotNull\(' . $attributePlaceholder . '\)'
                    .'$/',
                'expectedUseStatements' => new UseStatementCollection([
                    new UseStatement(ElementLocator::class),
                    new UseStatement(LocatorType::class),
                ]),
                'expectedVariablePlaceholders' => new VariablePlaceholderCollection([
                    $phpUnitTestCasePlaceholder,
                    $attributePlaceholder,
                    $elementLocatorPlaceholder,
                    $elementPlaceholder,
                    $hasPlaceholder,
                    $domCrawlerNavigatorPlaceholder,
                ]),
            ],
            'exists comparison, environment examined value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.KEY exists'
                ),
                'expectedContentPattern' =>
                    '/^'
                    . $environmentVariablePlaceholder
                    .' = '
                    . $environmentVariableArrayPlaceholder
                    . preg_quote('[\'KEY\'] ?? null', "/")
                    . "\n"
                    . $phpUnitTestCasePlaceholder
                    .'->assertNotNull\('
                    . $environmentVariablePlaceholder
                    .'\)/m',
                'expectedUseStatements' => new UseStatementCollection(),
                'expectedVariablePlaceholders' => VariablePlaceholderCollection::createCollection([
                    VariableNames::PHPUNIT_TEST_CASE,
                    'ENVIRONMENT_VARIABLE',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                ]),
            ],
            'exists comparison, browser object value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size exists'
                ),
                'expectedContentPattern' =>
                    '/^'
                    . $webDriverDimensionPlaceholder . ' = ' . $pantherClientPlaceholder . '.+' . "\n"
                    . $browserVariablePlaceholder . ' = .+' . "\n"
                    . $phpUnitTestCasePlaceholder .'->assertNotNull\(' . $browserVariablePlaceholder . '\)'
                    .'$/m',
                'expectedUseStatements' => new UseStatementCollection(),
                'expectedVariablePlaceholders' => VariablePlaceholderCollection::createCollection([
                    VariableNames::PHPUNIT_TEST_CASE,
                    'BROWSER_VARIABLE',
                    'WEBDRIVER_DIMENSION',
                    'BROWSER_SIZE',
                    VariableNames::PANTHER_CLIENT,
                ]),
            ],
            'exists comparison, page object value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title exists'
                ),
                'expectedContentPattern' =>
                    '/^'
                    . $pageVariablePlaceholder
                    .' = '
                    . $pantherClientPlaceholder
                    . '.+'
                    . "\n"
                    . $phpUnitTestCasePlaceholder
                    .'->assertNotNull\('
                    . $pageVariablePlaceholder
                    .'\)/m',
                'expectedUseStatements' => new UseStatementCollection(),
                'expectedVariablePlaceholders' => VariablePlaceholderCollection::createCollection([
                    VariableNames::PHPUNIT_TEST_CASE,
                    'PAGE_VARIABLE',
                    VariableNames::PANTHER_CLIENT,
                ]),
            ],
            'not-exists comparison, element identifier examined value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" not-exists'
                ),
                'expectedContentPattern' =>
                    '/^'
                    . $hasPlaceholder . ' = '. $domCrawlerNavigatorPlaceholder . '->has\(.*\)' . "\n"
                    . $phpUnitTestCasePlaceholder
                    . '->assertFalse\(' . $hasPlaceholder . '\)$/',
                'expectedUseStatements' => new UseStatementCollection([
                    new UseStatement(ElementLocator::class),
                    new UseStatement(LocatorType::class),
                ]),
                'expectedVariablePlaceholders' => new VariablePlaceholderCollection([
                    $phpUnitTestCasePlaceholder,
                    $domCrawlerNavigatorPlaceholder,
                    $hasPlaceholder,
                ]),
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name not-exists'
                ),
                'expectedContentPattern' => '/^'
                    . $elementLocatorPlaceholder . '.+' . "\n"
                    . $hasPlaceholder . ' = '. $domCrawlerNavigatorPlaceholder . '->hasOne\(.*\)' . "\n"
                    . $phpUnitTestCasePlaceholder . '->assertTrue\(' . $hasPlaceholder . '\)' . "\n"
                    . $elementPlaceholder . ' = '
                    . $domCrawlerNavigatorPlaceholder . '->findOne\(' . $elementLocatorPlaceholder . '\)' . "\n"
                    . $attributePlaceholder . ' = '
                    . $elementPlaceholder . '->getAttribute\(\'attribute_name\'\)' . "\n"
                    . $phpUnitTestCasePlaceholder . '->assertNull\(' . $attributePlaceholder . '\)'
                    .'$/',
                'expectedUseStatements' => new UseStatementCollection([
                    new UseStatement(ElementLocator::class),
                    new UseStatement(LocatorType::class),
                ]),
                'expectedVariablePlaceholders' => new VariablePlaceholderCollection([
                    $phpUnitTestCasePlaceholder,
                    $attributePlaceholder,
                    $elementLocatorPlaceholder,
                    $elementPlaceholder,
                    $hasPlaceholder,
                    $domCrawlerNavigatorPlaceholder,
                ]),
            ],
            'not-exists comparison, environment examined value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.KEY not-exists'
                ),
                'expectedContentPattern' =>
                    '/^'
                    . $environmentVariablePlaceholder
                    .' = '
                    . $environmentVariableArrayPlaceholder
                    . preg_quote('[\'KEY\'] ?? null', "/")
                    . "\n"
                    . $phpUnitTestCasePlaceholder
                    .'->assertNull\('
                    . $environmentVariablePlaceholder
                    .'\)/m',
                'expectedUseStatements' => new UseStatementCollection(),
                'expectedVariablePlaceholders' => VariablePlaceholderCollection::createCollection([
                    VariableNames::PHPUNIT_TEST_CASE,
                    'ENVIRONMENT_VARIABLE',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                ]),
            ],
        ];
    }

    public function testTranspileNonTranspilableModel()
    {
        $model = new ObjectValue('foo', '', '', '');

        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "webignition\BasilModel\Value\ObjectValue"');

        $this->transpiler->transpile($model);
    }
}
