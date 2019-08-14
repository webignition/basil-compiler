<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ValueInterface;

class LiteralValueTranspiler implements ValueTypeTranspilerInterface
{
    public static function createTranspiler(): ValueTypeTranspilerInterface
    {
        return new LiteralValueTranspiler();
    }

    public function handles(ValueInterface $value): bool
    {
        return $value instanceof LiteralValue;
    }

    public function transpile(ValueInterface $value): ?string
    {
        if (!$this->handles($value)) {
            return null;
        }

        return (string) $value;
    }
}
