<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

interface TranspilerInterface
{
    public static function createTranspiler();
    public function handles(object $model): bool;

    /**
     * @param object $model
     *
     * @return string
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): string;
}
