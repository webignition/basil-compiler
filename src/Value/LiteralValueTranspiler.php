<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilTranspiler\TranspilerInterface;

class LiteralValueTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): LiteralValueTranspiler
    {
        return new LiteralValueTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof LiteralValue;
    }

    public function transpile(object $model): ?string
    {
        if (!$this->handles($model)) {
            return null;
        }

        return (string) $model;
    }
}
