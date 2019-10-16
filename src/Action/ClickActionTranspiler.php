<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentFactory;
use webignition\BasilTranspiler\NamedDomIdentifierTranspiler;
use webignition\BasilTranspiler\TranspilerInterface;

class ClickActionTranspiler extends AbstractInteractionActionTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): ClickActionTranspiler
    {
        return new ClickActionTranspiler(
            VariableAssignmentFactory::createFactory(),
            NamedDomIdentifierTranspiler::createTranspiler()
        );
    }

    protected function getHandledActionType(): string
    {
        return ActionTypes::CLICK;
    }

    protected function getElementActionMethod(): string
    {
        return 'click';
    }
}
