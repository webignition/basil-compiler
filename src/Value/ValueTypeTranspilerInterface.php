<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ValueInterface;

interface ValueTypeTranspilerInterface
{
    public static function createTranspiler(): ValueTypeTranspilerInterface;
    public function handles(ValueInterface $value): bool;
    public function transpile(ValueInterface $value): ?string;
}
