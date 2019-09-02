<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
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
        return $model instanceof LiteralValue;
    }

    public function transpile(object $model): TranspilationResultInterface
    {
        if ($this->handles($model)) {
            return new TranspilationResult(
                [(string) $model],
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            );
        }

        throw new NonTranspilableModelException($model);
    }
}
