<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\InteractionActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentFactory;
use webignition\BasilTranspiler\Model\NamedDomIdentifier;
use webignition\BasilTranspiler\NamedDomIdentifierTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

abstract class AbstractInteractionActionTranspiler implements TranspilerInterface
{
    private $variableAssignmentFactory;
    private $namedDomIdentifierTranspiler;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        NamedDomIdentifierTranspiler $namedDomIdentifierTranspiler
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->namedDomIdentifierTranspiler = $namedDomIdentifierTranspiler;
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
        $elementPlaceholder = $variableExports->create('ELEMENT');

        $accessor = $this->namedDomIdentifierTranspiler->transpile(new NamedDomIdentifier(
            $identifier,
            $elementPlaceholder
        ));

        return (new CompilableSource())
            ->withPredecessors([$accessor])
            ->withStatements([sprintf(
                '%s->%s()',
                (string) $elementPlaceholder,
                $this->getElementActionMethod()
            )]);
    }
}
