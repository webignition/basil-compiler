<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Identifier;

use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class IdentifierTranspiler implements TranspilerInterface
{
    /**
     * @var TranspilerInterface[]
     */
    private $identifierTypeTranspilers = [];

    public function __construct(array $identifierTypeTranspilers = [])
    {
        foreach ($identifierTypeTranspilers as $identifierTypeTranspiler) {
            if ($identifierTypeTranspiler instanceof TranspilerInterface) {
                $this->addIdentifierTypeTranspiler($identifierTypeTranspiler);
            }
        }
    }

    public static function createTranspiler(): IdentifierTranspiler
    {
        return new IdentifierTranspiler([
            ElementIdentifierTranspiler::createTranspiler(),
        ]);
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
     *
     * @return string
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): string
    {
        if ($model instanceof IdentifierInterface) {
            $identifierTypeTranspiler = $this->findIdentifierTypeTranspiler($model);

            if ($identifierTypeTranspiler instanceof TranspilerInterface) {
                return $identifierTypeTranspiler->transpile($model);
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
