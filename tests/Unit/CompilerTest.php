<?php

namespace webignition\BasilCompiler\Tests\Unit;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompiler\Compiler;
use webignition\BasilCompiler\Tests\ExternalVariableIdentifiers;
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilModelFactory\AssertionFactory;

class CompilerTest extends TestCase
{
    /**
     * @var Compiler
     */
    private $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = Compiler::create();
    }

    /**
     * @dataProvider compileDataProvider
     */
    public function testCompile(TestInterface $test, ?string $baseClass, string $expectedCode)
    {
        $generatedCode = $this->compiler->compile(
            $test,
            $baseClass,
            ExternalVariableIdentifiers::IDENTIFIERS
        );

        echo "\n" . $generatedCode . "\n\n";

        $this->assertEquals($expectedCode, $generatedCode);
    }

    public function compileDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'no steps' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    []
                ),
                'baseClass' => TestCase::class,
                'expectedCode' =>
                    'use PHPUnit\Framework\TestCase;' . "\n" .
                    "\n" .
                    'class Generated69ef658fb6e99440777d8bbe69f5bc89Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$client->request(\'GET\', \'http://example.com\');
    }
}',
            ],
            'has step with action and assertion' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step one' => new Step(
                            [
                                $actionFactory->createFromActionString('click ".selector"'),
                            ],
                            [
                                $assertionFactory->createFromAssertionString('$page.title is "Page Title"')
                            ]
                        )
                    ]
                ),
                'baseClass' => TestCase::class,
                'expectedCode' =>
                    'use webignition\DomElementLocator\ElementLocator;' . "\n" .
                    'use PHPUnit\Framework\TestCase;' . "\n" .
                    "\n" .
                    'class Generated69ef658fb6e99440777d8bbe69f5bc89Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$client->request(\'GET\', \'http://example.com\');
    }

    public function testBdc4b8bd83e5660d1c62908dc7a7c43a()
    {
        // step one
        // click ".selector"
        $has = $this->navigator->hasOne(new ElementLocator(\'.selector\'));
        $this->assertTrue($has);
        $element = $this->navigator->findOne(new ElementLocator(\'.selector\'));
        $element->click();

        // $page.title is "Page Title"
        $expected = "Page Title" ?? null;
        $expected = (string) $expected;
        $examined = self::$client->getTitle() ?? null;
        $examined = (string) $examined;
        $this->assertEquals($expected, $examined);

    }
}',
            ],
            'has step with assertion utilising data set' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step one' => (new Step(
                            [],
                            [
                                $assertionFactory->createFromAssertionString('$page.title is $data.expected_title')
                            ]
                        ))->withDataSetCollection(new DataSetCollection([
                            new DataSet(
                                'setZero',
                                [
                                    'expected_title' => 'Page Title',
                                ]
                            )
                        ]))
                    ]
                ),
                'baseClass' => TestCase::class,
                'expectedCode' =>
                    'use PHPUnit\Framework\TestCase;' . "\n" .
                    "\n" .
                    'class Generated69ef658fb6e99440777d8bbe69f5bc89Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$client->request(\'GET\', \'http://example.com\');
    }

    /**
     * @dataProvider Bdc4b8bd83e5660d1c62908dc7a7c43aDataProvider
     */
    public function testBdc4b8bd83e5660d1c62908dc7a7c43a($expected_title)
    {
        // step one
        // $page.title is $data.expected_title
        $expected = $expected_title ?? null;
        $expected = (string) $expected;
        $examined = self::$client->getTitle() ?? null;
        $examined = (string) $examined;
        $this->assertEquals($expected, $examined);

    }

    public function Bdc4b8bd83e5660d1c62908dc7a7c43aDataProvider()
    {
        return [
            \'setZero\' => [
                \'expected_title\' => \'Page Title\',
            ],
        ];
    }
}',
            ],
        ];
    }
}
