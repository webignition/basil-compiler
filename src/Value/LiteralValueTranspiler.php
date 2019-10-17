<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class LiteralValueTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): LiteralValueTranspiler
    {
        return new LiteralValueTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof LiteralValueInterface;
    }

    public function transpile(object $model): SourceInterface
    {
        if ($this->handles($model)) {
            return (new Source())->withStatements([
                (string) $model,
            ]);
        }

        throw new NonTranspilableModelException($model);
    }
}
