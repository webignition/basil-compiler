<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

interface TranspilerInterface
{
    public static function createTranspiler();
    public function handles(object $model): bool;
    public function transpile(object $model): ?string;
}
