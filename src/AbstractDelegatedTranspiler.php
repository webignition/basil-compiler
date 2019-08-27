<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilTranspiler\Model\TranspilationResult;

abstract class AbstractDelegatedTranspiler implements TranspilerInterface
{
    private $variableNameResolver;

    /**
     * @var TranspilerInterface[]
     */
    private $delegatedTranspilers = [];

    public function __construct(VariableNameResolver $variableNameResolver, array $delegatedTranspilers = [])
    {
        $this->variableNameResolver = $variableNameResolver;

        foreach ($delegatedTranspilers as $delegatedTranspiler) {
            if ($delegatedTranspiler instanceof TranspilerInterface) {
                $this->addDelegatedTranspiler($delegatedTranspiler);
            }
        }
    }

    public function addDelegatedTranspiler(TranspilerInterface $transpiler)
    {
        $this->delegatedTranspilers[] = $transpiler;
    }

    /**
     * @param object $model
     * @param array $variableIdentifiers
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model, array $variableIdentifiers = []): TranspilationResult
    {
        $identifierTypeTranspiler = $this->findIdentifierTypeTranspiler($model);

        if ($identifierTypeTranspiler instanceof TranspilerInterface) {
            $transpilationResult = $identifierTypeTranspiler->transpile($model, $variableIdentifiers);

            $resolvedContent = $this->variableNameResolver->resolve(
                $transpilationResult->getContent(),
                $variableIdentifiers
            );

            return $transpilationResult->withContent($resolvedContent);
        }

        throw new NonTranspilableModelException($model);
    }

    protected function findIdentifierTypeTranspiler(object $model): ?TranspilerInterface
    {
        foreach ($this->delegatedTranspilers as $valueTypeTranspiler) {
            if ($valueTypeTranspiler->handles($model)) {
                return $valueTypeTranspiler;
            }
        }

        return null;
    }
}
