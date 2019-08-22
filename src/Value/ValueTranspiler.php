<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNameResolver;

class ValueTranspiler implements TranspilerInterface
{
    private $variableNameResolver;

    /**
     * @var TranspilerInterface[]
     */
    private $valueTypeTranspilers = [];

    public function __construct(VariableNameResolver $variableNameResolver, array $valueTypeTranspilers = [])
    {
        $this->variableNameResolver = $variableNameResolver;

        foreach ($valueTypeTranspilers as $valueTypeTranspiler) {
            if ($valueTypeTranspiler instanceof TranspilerInterface) {
                $this->addValueTypeTranspiler($valueTypeTranspiler);
            }
        }
    }

    public static function createTranspiler(): ValueTranspiler
    {
        return new ValueTranspiler(
            new VariableNameResolver(),
            [
                LiteralValueTranspiler::createTranspiler(),
                BrowserObjectValueTranspiler::createTranspiler(),
                PageObjectValueTranspiler::createTranspiler(),
                EnvironmentParameterValueTranspiler::createTranspiler(),
                ElementValueTranspiler::createTranspiler(),
            ]
        );
    }

    public function addValueTypeTranspiler(TranspilerInterface $transpiler)
    {
        $this->valueTypeTranspilers[] = $transpiler;
    }

    public function handles(object $model): bool
    {
        if ($model instanceof ValueInterface) {
            return null !== $this->findValueTypeTranspiler($model);
        }

        return false;
    }

    /**
     * @param object $model
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model, array $variableIdentifiers = []): string
    {
        if ($model instanceof ValueInterface) {
            $valueTypeTranspiler = $this->findValueTypeTranspiler($model);

            if ($valueTypeTranspiler instanceof TranspilerInterface) {
                return $this->variableNameResolver->resolve(
                    $valueTypeTranspiler->transpile($model),
                    $variableIdentifiers
                );
            }
        }

        throw new NonTranspilableModelException($model);
    }

    private function findValueTypeTranspiler(ValueInterface $value): ?TranspilerInterface
    {
        foreach ($this->valueTypeTranspilers as $valueTypeTranspiler) {
            if ($valueTypeTranspiler->handles($value)) {
                return $valueTypeTranspiler;
            }
        }

        return null;
    }
}
