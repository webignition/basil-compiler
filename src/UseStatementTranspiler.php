<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatement;

class UseStatementTranspiler implements TranspilerInterface
{
    const CLASS_NAME_ONLY_TEMPLATE = 'use %s';
    const WITH_ALIAS_TEMPLATE = self::CLASS_NAME_ONLY_TEMPLATE . ' as %s';

    public static function createTranspiler(): UseStatementTranspiler
    {
        return new UseStatementTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof UseStatement;
    }

    /**
     * @param object $model
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): TranspilationResult
    {
        if (!$model instanceof UseStatement) {
            throw new NonTranspilableModelException($model);
        }

        $alias = $model->getAlias();

        $content = null === $alias
            ? sprintf(self::CLASS_NAME_ONLY_TEMPLATE, $model->getClassName())
            : sprintf(self::WITH_ALIAS_TEMPLATE, $model->getClassName(), $model->getAlias());

        return new TranspilationResult($content);
    }
}
