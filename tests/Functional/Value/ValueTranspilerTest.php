<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Value;

use Facebook\WebDriver\WebDriverDimension;
use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\BasilTranspiler\Tests\Services\ExecutableCallFactory;
use webignition\BasilTranspiler\Value\ValueTranspiler;
use webignition\BasilTranspiler\VariableNames;

class ValueTranspilerTest extends AbstractTestCase
{
    const PANTHER_CLIENT_VARIABLE_NAME = 'self::$client';
    const VARIABLE_IDENTIFIERS = [
        VariableNames::PANTHER_CLIENT => self::PANTHER_CLIENT_VARIABLE_NAME,
    ];

    /**
     * @var ValueTranspiler
     */
    private $transpiler;

    /**
     * @var ExecutableCallFactory
     */
    private $executableCallFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = ValueTranspiler::createTranspiler();
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }

    /**
     * @dataProvider transpileDataProvider
     */
    public function testTranspile(
        string $fixture,
        ValueInterface $model,
        VariablePlaceholderCollection $expectedVariablePlaceholders,
        $expectedExecutedResult
    ) {
        $transpilationResult = $this->transpiler->transpile($model);

        $this->assertEquals([], $transpilationResult->getUseStatements()->getAll());
        $this->assertEquals(
            $expectedVariablePlaceholders->getAll(),
            $transpilationResult->getVariablePlaceholders()->getAll()
        );

        $executableCall = $this->executableCallFactory->createWithReturn(
            $transpilationResult,
            self::VARIABLE_IDENTIFIERS,
            [
                'self::$client->request(\'GET\', \'' . $fixture . '\'); ',
            ]
        );

        $this->assertEquals($expectedExecutedResult, eval($executableCall));
    }

    public function transpileDataProvider(): array
    {
        return [
            'browser object property: size' => [
                'fixture' => '/basic.html',
                'model' => new ObjectValue(
                    ValueTypes::BROWSER_OBJECT_PROPERTY,
                    '$browser.size',
                    ObjectNames::BROWSER,
                    'size'
                ),
                'expectedVariablePlaceholders' => new VariablePlaceholderCollection([
                    new VariablePlaceholder(VariableNames::PANTHER_CLIENT),
                ]),
                'expectedExecutedResult' => new WebDriverDimension(1200, 1100),
            ],
            'page object property: title' => [
                'fixture' => '/basic.html',
                'model' => new ObjectValue(
                    ValueTypes::PAGE_OBJECT_PROPERTY,
                    '$page.title',
                    ObjectNames::PAGE,
                    'title'
                ),
                'expectedVariablePlaceholders' => new VariablePlaceholderCollection([
                    new VariablePlaceholder(VariableNames::PANTHER_CLIENT),
                ]),
                'expectedExecutedResult' => 'A basic page',
            ],
            'page object property: url' => [
                'fixture' => '/basic.html',
                'model' => new ObjectValue(
                    ValueTypes::PAGE_OBJECT_PROPERTY,
                    '$page.url',
                    ObjectNames::PAGE,
                    'url'
                ),
                'expectedVariablePlaceholders' => new VariablePlaceholderCollection([
                    new VariablePlaceholder(VariableNames::PANTHER_CLIENT),
                ]),
                'expectedExecutedResult' => 'http://127.0.0.1:9080/basic.html',
            ],
        ];
    }
}
