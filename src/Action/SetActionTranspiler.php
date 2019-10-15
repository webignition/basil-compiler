<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\InputActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\CallFactory\WebDriverElementMutatorCallFactory;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\NonTranspilableValueException;
use webignition\BasilTranspiler\TranspilerInterface;

class SetActionTranspiler implements TranspilerInterface
{
    private $variableAssignmentCallFactory;
    private $webDriverElementMutatorCallFactory;

    public function __construct(
        VariableAssignmentCallFactory $variableAssignmentCallFactory,
        WebDriverElementMutatorCallFactory $webDriverElementMutatorCallFactory
    ) {
        $this->variableAssignmentCallFactory = $variableAssignmentCallFactory;
        $this->webDriverElementMutatorCallFactory = $webDriverElementMutatorCallFactory;
    }

    public static function createTranspiler(): SetActionTranspiler
    {
        return new SetActionTranspiler(
            VariableAssignmentCallFactory::createFactory(),
            WebDriverElementMutatorCallFactory::createFactory()
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

        $collectionAssignment = $this->variableAssignmentCallFactory->createForElementCollection(
            $identifier,
            $elementLocatorPlaceholder,
            $collectionPlaceholder
        );

        try {
            $valueAssignment = $this->variableAssignmentCallFactory->createForValue(
                $model->getValue(),
                $valuePlaceholder
            );
        } catch (NonTranspilableValueException $nonTranspilableValueException) {
            throw new NonTranspilableModelException($model);
        }

        $mutationCall = $this->webDriverElementMutatorCallFactory->createSetValueCall(
            $collectionPlaceholder,
            $valuePlaceholder
        );

        return (new CompilableSource())
            ->withPredecessors([$collectionAssignment, $valueAssignment, $mutationCall]);
    }
}
