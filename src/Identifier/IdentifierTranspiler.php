<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Identifier;

use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilTranspiler\AbstractDelegatingTranspiler;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNameResolver;

class IdentifierTranspiler extends AbstractDelegatingTranspiler implements TranspilerInterface
{
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

    public function handles(object $model): bool
    {
        if ($model instanceof IdentifierInterface) {
            return null !== $this->findDelegatedTranspiler($model);
        }

        return false;
    }
}
