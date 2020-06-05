<?php

namespace webignition\BasilCompiler;

use webignition\BasilCompilableSource\ClassDefinition;
use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilModels\Test\TestInterface;

class Compiler
{
    private ClassDefinitionFactory $classDefinitionFactory;
    private ExternalVariableIdentifiers $externalVariableIdentifiers;
    private VariablePlaceholderResolver $variablePlaceholderResolver;

    public function __construct(
        ClassDefinitionFactory $classDefinitionFactory,
        ExternalVariableIdentifiers $externalVariableIdentifiers,
        VariablePlaceholderResolver $variablePlaceholderResolver
    ) {
        $this->classDefinitionFactory = $classDefinitionFactory;
        $this->externalVariableIdentifiers = $externalVariableIdentifiers;
        $this->variablePlaceholderResolver = $variablePlaceholderResolver;
    }

    public static function create(ExternalVariableIdentifiers $externalVariableIdentifiers): Compiler
    {
        return new Compiler(
            ClassDefinitionFactory::createFactory(),
            $externalVariableIdentifiers,
            new VariablePlaceholderResolver()
        );
    }

    /**
     * @param TestInterface $test
     * @param string $fullyQualifiedBaseClass
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     * @throws UnsupportedStepException
     */
    public function compile(TestInterface $test, string $fullyQualifiedBaseClass): string
    {
        $classDefinition = $this->createClassDefinition($test);

        if ($classDefinition instanceof ClassDefinition) {
            $classDefinition->setBaseClass(new ClassDependency($fullyQualifiedBaseClass));
        }

        $compiledClass = $classDefinition->render();
        $compiledClassLines = explode("\n", $compiledClass);

        $resolvedLines = [];

        foreach ($compiledClassLines as $line) {
            $resolvedLines[] = $this->variablePlaceholderResolver->resolve(
                $line,
                $this->externalVariableIdentifiers->get()
            );
        }

        return implode("\n", $resolvedLines);
    }

    /**
     * @param TestInterface $test
     *
     * @return string
     *
     * @throws UnsupportedStepException
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
     * @throws UnsupportedStepException
     */
    private function createClassDefinition(TestInterface $test): ClassDefinitionInterface
    {
        return $this->classDefinitionFactory->createClassDefinition($test);
    }
}
