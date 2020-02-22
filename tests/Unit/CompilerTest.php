<?php

namespace webignition\BasilCompiler\Tests\Unit;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompiler\Compiler;
use webignition\BasilCompiler\ExternalVariableIdentifiers;
use webignition\BasilCompiler\Tests\Services\FixturePathFinder;
use webignition\BasilCompiler\Tests\Services\ObjectReflector;
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
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com',
                    ],
                ])->withPath('no-steps.yml'),
                'baseClass' => TestCase::class,
                'expectedCode' => file_get_contents(FixturePathFinder::find('GeneratedCode/GeneratedNoStepsTest.txt')),
            ],
            'has step with action and assertion' => [
                'generatedClassName' => 'GeneratedHasActionHasAssertionTest',
                'test' => $testParser->parse([
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
                ])->withPath('with-action-and-assertion.yml'),
                'baseClass' => TestCase::class,
                'expectedCode' => file_get_contents(FixturePathFinder::find(
                    'GeneratedCode/GeneratedHasActionHasAssertionTest.txt'
                )),
            ],
            'has step with assertion utilising data set' => [
                'generatedClassName' => 'GeneratedHasAssertionWithDataTest',
                'test' => $testParser->parse([
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
                ])->withPath('with-action-and-assertion-utilising-data.yml'),
                'baseClass' => TestCase::class,
                'expectedCode' => file_get_contents(FixturePathFinder::find(
                    'GeneratedCode/GeneratedHasAssertionWithDataTest.txt'
                )),
            ],
        ];
    }

    public function testCreateClassName(): void
    {
        $test = (new Test(
            new Configuration('chrome', 'http://example.com'),
            []
        ))->withPath('test.yml');

        $className = $this->compiler->createClassName($test);

        $this->assertEquals('GeneratedD894ed67e2008e18887400a33f7d82b3Test', $className);
    }

    private function setGeneratedClassName(Compiler $compiler, TestInterface $test, string $className): void
    {
        $classDefinitionFactory = ObjectReflector::getProperty($compiler, 'classDefinitionFactory');
        $classNameFactory = ObjectReflector::getProperty($classDefinitionFactory, 'classNameFactory');

        $mockClassNameFactory = \Mockery::mock($classNameFactory);
        $mockClassNameFactory
            ->shouldReceive('create')
            ->with($test)
            ->andReturn($className);

        ObjectReflector::setProperty(
            $classDefinitionFactory,
            ClassDefinitionFactory::class,
            'classNameFactory',
            $mockClassNameFactory
        );

        ObjectReflector::setProperty(
            $compiler,
            Compiler::class,
            'classDefinitionFactory',
            $classDefinitionFactory
        );
    }
}
