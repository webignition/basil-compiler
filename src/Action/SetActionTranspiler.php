<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\InputActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentFactory;
use webignition\BasilTranspiler\CallFactory\WebDriverElementMutatorCallFactory;
use webignition\BasilTranspiler\Model\NamedDomIdentifierValue;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\Value\ValueTranspiler;

class SetActionTranspiler implements TranspilerInterface
{
    private $variableAssignmentFactory;
    private $webDriverElementMutatorCallFactory;
    private $valueTranspiler;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        WebDriverElementMutatorCallFactory $webDriverElementMutatorCallFactory,
        ValueTranspiler $valueTranspiler
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->webDriverElementMutatorCallFactory = $webDriverElementMutatorCallFactory;
        $this->valueTranspiler = $valueTranspiler;
    }

    public static function createTranspiler(): SetActionTranspiler
    {
        return new SetActionTranspiler(
            VariableAssignmentFactory::createFactory(),
            WebDriverElementMutatorCallFactory::createFactory(),
            ValueTranspiler::createTranspiler()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof InputActionInterface;
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
        $elementLocatorPlaceholder = $variableExports->create('ELEMENT_LOCATOR');
        $collectionPlaceholder = $variableExports->create('COLLECTION');
        $valuePlaceholder = $variableExports->create('VALUE');

        $collectionAssignment = $this->variableAssignmentFactory->createForElementCollection(
            $identifier,
            $elementLocatorPlaceholder,
            $collectionPlaceholder
        );

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

        return (new CompilableSource())
            ->withPredecessors([$collectionAssignment, $valueAssignment, $mutationCall]);
    }
}
