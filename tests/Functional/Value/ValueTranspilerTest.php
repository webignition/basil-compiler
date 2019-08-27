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
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\BasilTranspiler\Tests\Services\ExecutableCallFactory;
use webignition\BasilTranspiler\Value\ValueTranspiler;
use webignition\BasilTranspiler\VariableNames;

class ValueTranspilerTest extends AbstractTestCase
{
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
        $expectedExecutedResult
    ) {
        $variableIdentifiers = [
            VariableNames::PANTHER_CLIENT => 'self::$client',
        ];

        $transpilationResult = $this->transpiler->transpile($model, $variableIdentifiers);

        $executableCall = $this->executableCallFactory->create(
            $transpilationResult,
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
                'value' => new ObjectValue(
                    ValueTypes::BROWSER_OBJECT_PROPERTY,
                    '$browser.size',
                    ObjectNames::BROWSER,
                    'size'
                ),
                'expectedExecutedResult' => new WebDriverDimension(1200, 1100),
            ],
            'page object property: title' => [
                'fixture' => '/basic.html',
                'value' => new ObjectValue(
                    ValueTypes::PAGE_OBJECT_PROPERTY,
                    '$page.title',
                    ObjectNames::PAGE,
                    'title'
                ),
                'expectedExecutedResult' => 'A basic page',
            ],
            'page object property: url' => [
                'fixture' => '/basic.html',
                'value' => new ObjectValue(
                    ValueTypes::PAGE_OBJECT_PROPERTY,
                    '$page.url',
                    ObjectNames::PAGE,
                    'url'
                ),
                'expectedExecutedResult' => 'http://127.0.0.1:9080/basic.html',
            ],
        ];
    }
}
