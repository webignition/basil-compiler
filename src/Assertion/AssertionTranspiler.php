<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilTranspiler\AbstractDelegatingTranspiler;
use webignition\BasilTranspiler\TranspilerInterface;

class AssertionTranspiler extends AbstractDelegatingTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): AssertionTranspiler
    {
        return new AssertionTranspiler();
    }

    public function handles(object $model): bool
    {
        if ($model instanceof AssertionTranspiler) {
            return null !== $this->findDelegatedTranspiler($model);
        }

        return false;
    }
}
