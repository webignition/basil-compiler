<?php

namespace webignition\BasilCompiler;

use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;

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
     * @param ClassDefinitionInterface $classDefinition
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function compile(ClassDefinitionInterface $classDefinition): string
    {
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
}
