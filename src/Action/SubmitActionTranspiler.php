<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentFactory;
use webignition\BasilTranspiler\NamedDomIdentifierTranspiler;
use webignition\BasilTranspiler\TranspilerInterface;

class SubmitActionTranspiler extends AbstractInteractionActionTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): SubmitActionTranspiler
    {
        return new SubmitActionTranspiler(
            VariableAssignmentFactory::createFactory(),
            NamedDomIdentifierTranspiler::createTranspiler()
        );
    }

    protected function getHandledActionType(): string
    {
        return ActionTypes::SUBMIT;
    }

    protected function getElementActionMethod(): string
    {
        return 'submit';
    }
}
