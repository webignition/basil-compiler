<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilTranspiler\Model\TranspilableSource;
use webignition\BasilTranspiler\Model\TranspilableSourceInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
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

    public function transpile(object $model): TranspilableSourceInterface
    {
        if ($this->handles($model)) {
            return new TranspilableSource(
                [(string) $model],
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            );
        }

        throw new NonTranspilableModelException($model);
    }
}
