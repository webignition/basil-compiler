<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;

class ClassDependencyTranspiler implements TranspilerInterface
{
    const CLASS_NAME_ONLY_TEMPLATE = 'use %s';
    const WITH_ALIAS_TEMPLATE = self::CLASS_NAME_ONLY_TEMPLATE . ' as %s';

    public static function createTranspiler(): ClassDependencyTranspiler
    {
        return new ClassDependencyTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof ClassDependency;
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): SourceInterface
    {
        if (!$model instanceof ClassDependency) {
            throw new NonTranspilableModelException($model);
        }

        $alias = $model->getAlias();

        $statement = null === $alias
            ? sprintf(self::CLASS_NAME_ONLY_TEMPLATE, $model->getClassName())
            : sprintf(self::WITH_ALIAS_TEMPLATE, $model->getClassName(), $model->getAlias());

        return (new Source())->withStatements([$statement]);
    }
}
