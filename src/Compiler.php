<?php

namespace webignition\BasilCompiler;

use webignition\BasilCodeGenerator\ClassGenerator;
use webignition\BasilCodeGenerator\UnresolvedPlaceholderException;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinitionInterface;
use webignition\BasilModel\Test\TestInterface;

class Compiler
{
    private $classDefinitionFactory;
    private $classGenerator;
    private $variableIdentifierGenerator;
    private $externalVariableIdentifiers;

    public function __construct(
        ClassDefinitionFactory $classDefinitionFactory,
        ClassGenerator $classGenerator,
        VariableIdentifierGenerator $variableIdentifierGenerator,
        ExternalVariableIdentifiers $externalVariableIdentifiers
    ) {
        $this->classDefinitionFactory = $classDefinitionFactory;
        $this->classGenerator = $classGenerator;
        $this->variableIdentifierGenerator = $variableIdentifierGenerator;
        $this->externalVariableIdentifiers = $externalVariableIdentifiers;
    }

    public static function create(ExternalVariableIdentifiers $externalVariableIdentifiers): Compiler
    {
        return new Compiler(
            ClassDefinitionFactory::createFactory(),
            ClassGenerator::create(),
            new VariableIdentifierGenerator(),
            $externalVariableIdentifiers
        );
    }

    /**
     * @param TestInterface $test
     * @param string $fullyQualifiedBaseClass
     *
     * @return string
     *
     * @throws UnknownObjectPropertyException
     * @throws UnresolvedPlaceholderException
     * @throws UnsupportedModelException
     */
    public function compile(
        TestInterface $test,
        string $fullyQualifiedBaseClass
    ): string {
        $classDefinition = $this->createClassDefinition($test);

        $metadata = $classDefinition->getMetadata();
        $variableExportIdentifiers = $this->variableIdentifierGenerator->generate($metadata->getVariableExports());

        return $this->classGenerator->createForClassDefinition(
            $classDefinition,
            $fullyQualifiedBaseClass,
            array_merge($this->externalVariableIdentifiers->get(), $variableExportIdentifiers)
        );
    }

    /**
     * @param TestInterface $test
     *
     * @return string
     *
     * @throws UnknownObjectPropertyException
     * @throws UnsupportedModelException
     */
    public function createClassName(TestInterface $test): string
    {
        return $this->createClassDefinition($test)->getName();
    }

    /**
     * @param TestInterface $test
     *
     * @return ClassDefinitionInterface
     *
     * @throws UnknownObjectPropertyException
     * @throws UnsupportedModelException
     */
    private function createClassDefinition(TestInterface $test): ClassDefinitionInterface
    {
        return $this->classDefinitionFactory->createClassDefinition($test);
    }
}
