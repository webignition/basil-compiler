<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class DomIdentifierValueTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): DomIdentifierValueTranspiler
    {
        return new DomIdentifierValueTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof DomIdentifierValueInterface;
    }

    public function transpile(object $model): CompilableSourceInterface
    {
        throw new NonTranspilableModelException($model);
    }
}
