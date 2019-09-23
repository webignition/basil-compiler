<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilTranspiler\AbstractDelegatingTranspiler;
use webignition\BasilTranspiler\TranspilerInterface;

class ActionTranspiler extends AbstractDelegatingTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): ActionTranspiler
    {
        return new ActionTranspiler(
            [
                WaitActionTranspiler::createTranspiler(),
            ]
        );
    }

    public function handles(object $model): bool
    {
        if ($model instanceof ActionInterface) {
            return null !== $this->findDelegatedTranspiler($model);
        }

        return false;
    }
}
