<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Value;

use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\BasilTranspiler\Value\ValueTranspiler;
use webignition\BasilTranspiler\VariableNames;

class ValueTranspilerTest extends AbstractTestCase
{
    /**
     * @var ValueTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = ValueTranspiler::createTranspiler();
    }

    /**
     * @dataProvider transpileDataProvider
     */
    public function testTranspile(
        string $fixture,
        ValueInterface $model,
        VariablePlaceholderCollection $expectedVariablePlaceholders,
        $expectedExecutedResult,
        array $additionalVariableIdentifiers = []
    ) {
        $compilableSource = $this->transpiler->transpile($model);

        $this->assertEquals(new ClassDependencyCollection(), $compilableSource->getClassDependencies());
        $this->assertEquals($expectedVariablePlaceholders, $compilableSource->getVariablePlaceholders());

        $executableCall = $this->executableCallFactory->createWithReturn(
            $compilableSource,
            array_merge(
                self::VARIABLE_IDENTIFIERS,
                $additionalVariableIdentifiers
            ),
            [
                'self::$client->request(\'GET\', \'' . $fixture . '\'); ',
            ]
        );

        $this->assertEquals($expectedExecutedResult, eval($executableCall));
    }

    public function transpileDataProvider(): array
    {
        return [
            'browser property: size' => [
                'fixture' => '/empty.html',
                'model' => new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.size', 'size'),
                'expectedVariablePlaceholders' => VariablePlaceholderCollection::createCollection([
                    'WEBDRIVER_DIMENSION',
                    'BROWSER_SIZE',
                    VariableNames::PANTHER_CLIENT,
                ]),
                'expectedExecutedResult' => '1200x1100',
                'additionalVariableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                    'BROWSER_SIZE' => '$browser'
                ],
            ],
            'page property: title' => [
                'fixture' => '/index.html',
                'model' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.title', 'title'),
                'expectedVariablePlaceholders' => VariablePlaceholderCollection::createCollection([
                    VariableNames::PANTHER_CLIENT,
                ]),
                'expectedExecutedResult' => 'Test fixture web server default document',
            ],
            'page property: url' => [
                'fixture' => '/index.html',
                'model' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.url', 'url'),
                'expectedVariablePlaceholders' => VariablePlaceholderCollection::createCollection([
                    VariableNames::PANTHER_CLIENT,
                ]),
                'expectedExecutedResult' => 'http://127.0.0.1:9080/index.html',
            ],
        ];
    }
}
