<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\AbstractDelegatingTranspiler;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNameResolver;

class ValueTranspiler extends AbstractDelegatingTranspiler implements TranspilerInterface
{
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

    public function handles(object $model): bool
    {
        if ($model instanceof ValueInterface) {
            return null !== $this->findDelegatedTranspiler($model);
        }

        return false;
    }
}
