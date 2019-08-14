<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;

class LiteralStringValueTranspiler implements ValueTypeTranspilerInterface
{
    public static function createTranspiler(): ValueTypeTranspilerInterface
    {
        return new LiteralStringValueTranspiler();
    }

    public function handles(ValueInterface $value): bool
    {
        return ValueTypes::STRING === $value->getType();
    }

    public function transpile(ValueInterface $value): ?string
    {
        if (!$this->handles($value)) {
            return null;
        }

        return (string) $value;
    }
}
