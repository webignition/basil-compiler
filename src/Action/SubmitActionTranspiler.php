<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\TranspilableSourceComposer;
use webignition\BasilTranspiler\TranspilerInterface;

class SubmitActionTranspiler extends AbstractInteractionActionTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): SubmitActionTranspiler
    {
        return new SubmitActionTranspiler(
            VariableAssignmentCallFactory::createFactory(),
            TranspilableSourceComposer::create()
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
