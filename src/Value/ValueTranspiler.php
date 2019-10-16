<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\AbstractDelegatingTranspiler;
use webignition\BasilTranspiler\NamedDomIdentifierTranspiler;
use webignition\BasilTranspiler\TranspilerInterface;

class ValueTranspiler extends AbstractDelegatingTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): ValueTranspiler
    {
        return new ValueTranspiler(
            [
                BrowserPropertyTranspiler::createTranspiler(),
                EnvironmentParameterValueTranspiler::createTranspiler(),
                LiteralValueTranspiler::createTranspiler(),
                PagePropertyTranspiler::createTranspiler(),
                NamedDomIdentifierTranspiler::createTranspiler(),
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
