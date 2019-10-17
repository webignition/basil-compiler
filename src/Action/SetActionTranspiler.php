<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\InputActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentFactory;
use webignition\BasilTranspiler\CallFactory\WebDriverElementMutatorCallFactory;
use webignition\BasilTranspiler\Model\NamedDomIdentifier;
use webignition\BasilTranspiler\Model\NamedDomIdentifierValue;
use webignition\BasilTranspiler\NamedDomIdentifierTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\Value\ValueTranspiler;

class SetActionTranspiler implements TranspilerInterface
{
    private $variableAssignmentFactory;
    private $webDriverElementMutatorCallFactory;
    private $valueTranspiler;
    private $namedDomIdentifierTranspiler;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        WebDriverElementMutatorCallFactory $webDriverElementMutatorCallFactory,
        ValueTranspiler $valueTranspiler,
        NamedDomIdentifierTranspiler $namedDomIdentifierTranspiler
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->webDriverElementMutatorCallFactory = $webDriverElementMutatorCallFactory;
        $this->valueTranspiler = $valueTranspiler;
        $this->namedDomIdentifierTranspiler = $namedDomIdentifierTranspiler;
    }

    public static function createTranspiler(): SetActionTranspiler
    {
        return new SetActionTranspiler(
            VariableAssignmentFactory::createFactory(),
            WebDriverElementMutatorCallFactory::createFactory(),
            ValueTranspiler::createTranspiler(),
            NamedDomIdentifierTranspiler::createTranspiler()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof InputActionInterface;
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
        if (!$model instanceof InputActionInterface) {
            throw new NonTranspilableModelException($model);
        }

        $identifier = $model->getIdentifier();

        if (!$identifier instanceof DomIdentifierInterface) {
            throw new NonTranspilableModelException($model);
        }

        if (null !== $identifier->getAttributeName()) {
            throw new NonTranspilableModelException($model);
        }

        $variableExports = new VariablePlaceholderCollection();
        $collectionPlaceholder = $variableExports->create('COLLECTION');
        $valuePlaceholder = $variableExports->create('VALUE');

        $collectionAssignment = $this->namedDomIdentifierTranspiler->transpile(new NamedDomIdentifier(
            $identifier,
            $collectionPlaceholder
        ));

        $value = $model->getValue();

        if ($value instanceof DomIdentifierValueInterface) {
            $value = new NamedDomIdentifierValue($value, $valuePlaceholder);
        }

        $valueAccessor = $this->valueTranspiler->transpile($value);
        $valueAssignment = $this->variableAssignmentFactory->createForValueAccessor($valueAccessor, $valuePlaceholder);

        $mutationCall = $this->webDriverElementMutatorCallFactory->createSetValueCall(
            $collectionPlaceholder,
            $valuePlaceholder
        );

        return (new Source())
            ->withPredecessors([$collectionAssignment, $valueAssignment, $mutationCall]);
    }
}
