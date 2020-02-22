<?php

namespace webignition\BasilCompiler\Tests\Unit;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompiler\VariableIdentifierGenerator;

class VariableIdentifierGeneratorTest extends TestCase
{
    /**
     * @var VariableIdentifierGenerator
     */
    private $variableIdentifierGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->variableIdentifierGenerator = new VariableIdentifierGenerator();
    }

    /**
     * @param VariablePlaceholderCollection $variablePlaceholders
     * @param array<string, string> $expectedIdentifiers
     *
     * @dataProvider generateDataProvider
     */
    public function testGenerate(VariablePlaceholderCollection $variablePlaceholders, array $expectedIdentifiers): void
    {
        $this->assertEquals($expectedIdentifiers, $this->variableIdentifierGenerator->generate($variablePlaceholders));
    }

    public function generateDataProvider(): array
    {
        return [
            'empty' => [
                'variablePlaceholders' => VariablePlaceholderCollection::createDependencyCollection(),
                'expectedIdentifiers' => [],
            ],
            'non-empty' => [
                'variablePlaceholders' => VariablePlaceholderCollection::createDependencyCollection([
                    'HAS',
                    'ELEMENT',
                    'EXPECTED',
                ]),
                'expectedIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'EXPECTED' => '$expected',
                ],
            ],
        ];
    }
}
