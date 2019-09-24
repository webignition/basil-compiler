<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilModel\Action\InteractionActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilationResultComposer;
use webignition\BasilTranspiler\TranspilerInterface;

abstract class AbstractInteractionActionTranspiler implements TranspilerInterface
{
    private $variableAssignmentCallFactory;
    private $transpilationResultComposer;

    public function __construct(
        VariableAssignmentCallFactory $variableAssignmentCallFactory,
        TranspilationResultComposer $transpilationResultComposer
    ) {
        $this->variableAssignmentCallFactory = $variableAssignmentCallFactory;
        $this->transpilationResultComposer = $transpilationResultComposer;
    }

    abstract protected function getHandledActionType(): string;
    abstract protected function getElementActionMethod(): string;

    public function handles(object $model): bool
    {
        return $model instanceof InteractionActionInterface && $this->getHandledActionType() === $model->getType();
    }

    /**
     * @param object $model
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): TranspilationResultInterface
    {
        if (!$model instanceof InteractionActionInterface) {
            throw new NonTranspilableModelException($model);
        }

        if ($this->getHandledActionType() !== $model->getType()) {
            throw new NonTranspilableModelException($model);
        }

        $identifier = $model->getIdentifier();

        if (!$identifier instanceof DomIdentifierInterface) {
            throw new NonTranspilableModelException($model);
        }

        if (null !== $identifier->getAttributeName()) {
            throw new NonTranspilableModelException($model);
        }

        $variablePlaceholders = new VariablePlaceholderCollection();
        $elementLocatorPlaceholder = $variablePlaceholders->create('ELEMENT_LOCATOR');
        $elementPlaceholder = $variablePlaceholders->create('ELEMENT');

        $elementVariableAssignmentCall = $this->variableAssignmentCallFactory->createForElement(
            $identifier,
            $elementLocatorPlaceholder,
            $elementPlaceholder
        );

        $statements = $elementVariableAssignmentCall->getLines();
        $statements[] = sprintf(
            '%s->%s()',
            (string) $elementPlaceholder,
            $this->getElementActionMethod()
        );

        $calls = [
            $elementVariableAssignmentCall,
        ];

        return $this->transpilationResultComposer->compose(
            $statements,
            $calls,
            new UseStatementCollection(),
            $variablePlaceholders
        );
    }
}
