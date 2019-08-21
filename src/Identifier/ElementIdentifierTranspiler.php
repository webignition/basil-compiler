<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Identifier;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class ElementIdentifierTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): ElementIdentifierTranspiler
    {
        return new ElementIdentifierTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof ElementIdentifierInterface;
    }

    public function transpile(object $model): string
    {
        if ($model instanceof ElementIdentifierInterface) {
            var_dump('foo');
            exit();
        }

        throw new NonTranspilableModelException($model);
    }
}
