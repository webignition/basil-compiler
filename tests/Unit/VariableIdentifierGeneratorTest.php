<?php

namespace webignition\BasilCompiler\Tests\Unit;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
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
     * @dataProvider generateDataProvider
     */
    public function testGenerate(VariablePlaceholderCollection $variablePlaceholders, array $expectedIdentifiers)
    {
        $this->assertEquals($expectedIdentifiers, $this->variableIdentifierGenerator->generate($variablePlaceholders));
    }

    public function generateDataProvider(): array
    {
        return [
            'empty' => [
                'variablePlaceholders' => new VariablePlaceholderCollection(),
                'expectedIdentifiers' => [],
            ],
            'non-empty' => [
                'variablePlaceholders' => VariablePlaceholderCollection::createCollection([
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
