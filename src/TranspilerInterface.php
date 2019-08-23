<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilTranspiler\Model\TranspilationResult;

interface TranspilerInterface
{
    public static function createTranspiler();
    public function handles(object $model): bool;

    /**
     * @param object $model
     * @param array $variableIdentifiers
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model, array $variableIdentifiers = []): TranspilationResult;
}
