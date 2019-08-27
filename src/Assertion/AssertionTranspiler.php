<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilTranspiler\AbstractDelegatedTranspiler;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNameResolver;

class AssertionTranspiler extends AbstractDelegatedTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): AssertionTranspiler
    {
        return new AssertionTranspiler(
            new VariableNameResolver()
        );
    }

    public function handles(object $model): bool
    {
        if ($model instanceof AssertionTranspiler) {
            return null !== $this->findIdentifierTypeTranspiler($model);
        }

        return false;
    }
}
