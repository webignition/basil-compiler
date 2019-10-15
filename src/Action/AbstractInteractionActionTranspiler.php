<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\InteractionActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentFactory;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

abstract class AbstractInteractionActionTranspiler implements TranspilerInterface
{
    private $variableAssignmentFactory;

    public function __construct(VariableAssignmentFactory $variableAssignmentFactory)
    {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
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
     * @return CompilableSourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): CompilableSourceInterface
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

        $variableExports = new VariablePlaceholderCollection();
        $elementLocatorPlaceholder = $variableExports->create('ELEMENT_LOCATOR');
        $elementPlaceholder = $variableExports->create('ELEMENT');

        $elementAssignment = $this->variableAssignmentFactory->createForElement(
            $identifier,
            $elementLocatorPlaceholder,
            $elementPlaceholder
        );

        return (new CompilableSource())
            ->withPredecessors([$elementAssignment])
            ->withStatements([sprintf(
                '%s->%s()',
                (string) $elementPlaceholder,
                $this->getElementActionMethod()
            )]);
    }
}
