<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Identifier;

use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNameResolver;

class IdentifierTranspiler implements TranspilerInterface
{
    private $variableNameResolver;

    /**
     * @var TranspilerInterface[]
     */
    private $identifierTypeTranspilers = [];

    public function __construct(VariableNameResolver $variableNameResolver, array $identifierTypeTranspilers = [])
    {
        $this->variableNameResolver = $variableNameResolver;

        foreach ($identifierTypeTranspilers as $identifierTypeTranspiler) {
            if ($identifierTypeTranspiler instanceof TranspilerInterface) {
                $this->addIdentifierTypeTranspiler($identifierTypeTranspiler);
            }
        }
    }

    public static function createTranspiler(): IdentifierTranspiler
    {
        return new IdentifierTranspiler(
            new VariableNameResolver(),
            [
                ElementIdentifierTranspiler::createTranspiler(),
                AttributeIdentifierTranspiler::createTranspiler(),
            ]
        );
    }

    public function addIdentifierTypeTranspiler(TranspilerInterface $transpiler)
    {
        $this->identifierTypeTranspilers[] = $transpiler;
    }

    public function handles(object $model): bool
    {
        if ($model instanceof IdentifierInterface) {
            return null !== $this->findIdentifierTypeTranspiler($model);
        }

        return false;
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
        if ($model instanceof IdentifierInterface) {
            $identifierTypeTranspiler = $this->findIdentifierTypeTranspiler($model);

            if ($identifierTypeTranspiler instanceof TranspilerInterface) {
                $transpilationResult = $identifierTypeTranspiler->transpile($model, $variableIdentifiers);

                $resolvedContent = $this->variableNameResolver->resolve(
                    $transpilationResult->getContent(),
                    $variableIdentifiers
                );

                return $transpilationResult->withContent($resolvedContent);
            }
        }

        throw new NonTranspilableModelException($model);
    }

    private function findIdentifierTypeTranspiler(IdentifierInterface $identifier): ?TranspilerInterface
    {
        foreach ($this->identifierTypeTranspilers as $valueTypeTranspiler) {
            if ($valueTypeTranspiler->handles($identifier)) {
                return $valueTypeTranspiler;
            }
        }

        return null;
    }
}
