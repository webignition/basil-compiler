<?php

namespace webignition\BasilCompiler;

use webignition\BasilCodeGenerator\ClassGenerator;
use webignition\BasilCodeGenerator\UnresolvedPlaceholderException;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilModel\Test\TestInterface;

class Compiler
{
    private $classDefinitionFactory;
    private $classGenerator;
    private $variableIdentifierGenerator;

    public function __construct(
        ClassDefinitionFactory $classDefinitionFactory,
        ClassGenerator $classGenerator,
        VariableIdentifierGenerator $variableIdentifierGenerator
    ) {
        $this->classDefinitionFactory = $classDefinitionFactory;
        $this->classGenerator = $classGenerator;
        $this->variableIdentifierGenerator = $variableIdentifierGenerator;
    }

    public static function create(): Compiler
    {
        return new Compiler(
            ClassDefinitionFactory::createFactory(),
            ClassGenerator::create(),
            new VariableIdentifierGenerator()
        );
    }

    /**
     * @param TestInterface $test
     * @param string $fullyQualifiedBaseClass
     * @param array $externalVariableIdentifiers
     *
     * @return string
     *
     * @throws UnknownObjectPropertyException
     * @throws UnresolvedPlaceholderException
     * @throws UnsupportedModelException
     */
    public function compile(
        TestInterface $test,
        string $fullyQualifiedBaseClass,
        array $externalVariableIdentifiers = []
    ): string {
        $classDefinition = $this->classDefinitionFactory->createClassDefinition($test);

        $metadata = $classDefinition->getMetadata();
        $variableExportIdentifiers = $this->variableIdentifierGenerator->generate($metadata->getVariableExports());

        return $this->classGenerator->createForClassDefinition(
            $classDefinition,
            $fullyQualifiedBaseClass,
            array_merge($externalVariableIdentifiers, $variableExportIdentifiers)
        );
    }
}
