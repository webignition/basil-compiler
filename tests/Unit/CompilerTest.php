<?php

namespace webignition\BasilCompiler\Tests\Unit;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompiler\Compiler;
use webignition\BasilCompiler\ExternalVariableIdentifiers;
use webignition\BasilModels\Test\Configuration;
use webignition\BasilModels\Test\Test;
use webignition\BasilModels\Test\TestInterface;
use webignition\BasilParser\Test\TestParser;

class CompilerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Compiler
     */
    private $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = Compiler::create(new ExternalVariableIdentifiers(
            '$this->navigator',
            '$_ENV',
            'self::$client',
            'self::$crawler',
            '$this',
            'self::$inspector',
            'self::$mutator'
        ));
    }

    /**
     * @dataProvider compileDataProvider
     */
    public function testCompile(
        string $generatedClassName,
        TestInterface $test,
        string $baseClass,
        string $expectedCode
    ): void {
        $this->setGeneratedClassName($this->compiler, $test, $generatedClassName);

        $generatedCode = $this->compiler->compile($test, $baseClass);

        $this->assertEquals($expectedCode, $generatedCode);
    }

    public function compileDataProvider(): array
    {
        $testParser = TestParser::create();

        return [
            'no steps' => [
                'generatedClassName' => 'GeneratedNoStepsTest',
                'test' => $testParser->parse('', 'test.yml', [
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com',
                    ],
                ]),
                'baseClass' => TestCase::class,
                'expectedCode' =>
                    'use PHPUnit\Framework\TestCase;' . "\n" .
                    "\n" .
                    'class GeneratedNoStepsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$client->request(\'GET\', \'http://example.com\');
    }
}',
            ],
            'has step with action and assertion' => [
                'generatedClassName' => 'GeneratedHasActionHasAssertionTest',
                'test' => $testParser->parse('', 'test.yml', [
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com',
                    ],
                    'step one' => [
                        'actions' => [
                            'click $".selector"',
                        ],
                        'assertions' => [
                            '$page.title is "Page Title"',
                        ],
                    ],
                ]),
                'baseClass' => TestCase::class,
                'expectedCode' =>
                    'use webignition\DomElementLocator\ElementLocator;' . "\n" .
                    'use PHPUnit\Framework\TestCase;' . "\n" .
                    "\n" .
                    'class GeneratedHasActionHasAssertionTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$client->request(\'GET\', \'http://example.com\');
    }

    public function testBdc4b8bd83e5660d1c62908dc7a7c43a()
    {
        // step one
        // click $".selector"
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
                'generatedClassName' => 'GeneratedHasAssertionWithDataTest',
                'test' => $testParser->parse('', 'test.yml', [
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com',
                    ],
                    'step one' => [
                        'assertions' => [
                            '$page.title is $data.expected_title',
                        ],
                        'data' => [
                            'setZero' => [
                                'expected_title' => 'Page Title',
                            ],
                        ],
                    ],
                ]),
                'baseClass' => TestCase::class,
                'expectedCode' =>
                    'use PHPUnit\Framework\TestCase;' . "\n" .
                    "\n" .
                    'class GeneratedHasAssertionWithDataTest extends TestCase
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

    public function testCreateClassName(): void
    {
        $test = new Test('test name', new Configuration('chrome', 'http://example.com'), []);

        $className = $this->compiler->createClassName($test);

        $this->assertEquals('Generated69ef658fb6e99440777d8bbe69f5bc89Test', $className);
    }

    private function setGeneratedClassName(Compiler $compiler, TestInterface $test, string $className): void
    {
        $compilerReflector = new \ReflectionClass($compiler);
        $classDefinitionFactoryProperty = $compilerReflector->getProperty('classDefinitionFactory');
        $classDefinitionFactoryProperty->setAccessible(true);

        $classDefinitionFactory = $classDefinitionFactoryProperty->getValue($compiler);



        $classDefinitionFactoryReflector = new \ReflectionClass($classDefinitionFactory);
        $classNameFactoryProperty = $classDefinitionFactoryReflector->getProperty('classNameFactory');
        $classNameFactoryProperty->setAccessible(true);

        $classNameFactory = $classNameFactoryProperty->getValue($classDefinitionFactory);

        $mockClassNameFactory = \Mockery::mock($classNameFactory);
        $mockClassNameFactory
            ->shouldReceive('create')
            ->with($test)
            ->andReturn($className);

        $classNameFactoryProperty->setValue($classDefinitionFactory, $mockClassNameFactory);
        $classDefinitionFactoryProperty->setValue($compiler, $classDefinitionFactory);
    }
}
