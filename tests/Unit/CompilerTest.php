<?php

namespace webignition\BasilCompiler\Tests\Unit;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSource\ClassDefinition;
use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSource\Expression\ClassDependency;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompiler\Compiler;
use webignition\BasilCompiler\ExternalVariableIdentifiers;
use webignition\BasilCompiler\Tests\Services\FixturePathFinder;
use webignition\BasilCompiler\UnresolvedPlaceholderException;
use webignition\BasilModels\Test\TestInterface;
use webignition\BasilParser\Test\TestParser;
use webignition\ObjectReflector\ObjectReflector;

class CompilerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Compiler $compiler;

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
            'self::$mutator',
            '$this->actionFactory',
            '$this->assertionFactory'
        ));
    }

    /**
     * @dataProvider compileDataProvider
     */
    public function testCompile(ClassDefinitionInterface $classDefinition, string $expectedCode)
    {
        self::assertSame(
            $expectedCode,
            $this->compiler->compile($classDefinition)
        );
    }

    public function compileDataProvider(): array
    {
        $testParser = TestParser::create();

        return [
            'no steps' => [
                'classDefinition' => $this->createClassDefinitionWithBaseClass(
                    $testParser->parse([
                        'config' => [
                            'browser' => 'chrome',
                            'url' => 'http://example.com',
                        ],
                    ])->withPath('no-steps.yml'),
                    TestCase::class
                ),
                'expectedCode' => file_get_contents(FixturePathFinder::find(
                    'GeneratedCode/GeneratedB09e22d26fa517085105e76c53d0f0ebTest.txt'
                )),
            ],
            'has step with action and assertion' => [
                'classDefinition' => $this->createClassDefinitionWithBaseClass(
                    $testParser->parse([
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
                    TestCase::class
                ),
                'expectedCode' => file_get_contents(FixturePathFinder::find(
                    'GeneratedCode/Generated7aa1e217d2074ae763e26485d89f02efTest.txt'
                )),
            ],
            'has step with assertion utilising data set' => [
                'classDefinition' => $this->createClassDefinitionWithBaseClass(
                    $testParser->parse([
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
                    TestCase::class
                ),
                'expectedCode' => file_get_contents(FixturePathFinder::find(
                    'GeneratedCode/Generated3ac31ab525e5755af0442d0eabf38629Test.txt'
                )),
            ],
        ];
    }

    public function testCompileThrowsUnresolvedPlaceholderException()
    {
        $mockExternalVariableIdentifiers = \Mockery::mock(ExternalVariableIdentifiers::class);
        $mockExternalVariableIdentifiers
            ->shouldReceive('get')
            ->andReturn([]);

        ObjectReflector::setProperty(
            $this->compiler,
            Compiler::class,
            'externalVariableIdentifiers',
            $mockExternalVariableIdentifiers
        );

        $testParser = TestParser::create();

        $test = $testParser->parse([
            'config' => [
                'url' => 'http://example.com',
            ],
        ])->withPath('test.yml');

        $classDefinition = $this->createClassDefinitionWithBaseClass($test, TestCase::class);

        $this->expectException(UnresolvedPlaceholderException::class);
        $this->expectExceptionMessage(
            'Unresolved placeholder "CLIENT" in content "{{ CLIENT }}->request(\'GET\', \'http://example.com\');"'
        );

        $this->compiler->compile($classDefinition);
    }

    private function createClassDefinitionWithBaseClass(
        TestInterface $test,
        string $baseClass
    ): ClassDefinitionInterface {
        $classDefinition = ClassDefinitionFactory::createFactory()->createClassDefinition($test);
        if ($classDefinition instanceof ClassDefinition) {
            $classDefinition->setBaseClass(new ClassDependency($baseClass));
        }

        return $classDefinition;
    }
}
