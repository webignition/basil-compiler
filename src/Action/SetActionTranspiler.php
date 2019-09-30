<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilModel\Action\InputActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\CallFactory\WebDriverElementMutatorCallFactory;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\TranspilableSourceInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilableSourceComposer;
use webignition\BasilTranspiler\TranspilerInterface;

class SetActionTranspiler implements TranspilerInterface
{
    private $variableAssignmentCallFactory;
    private $transpilableSourceComposer;
    private $webDriverElementMutatorCallFactory;

    public function __construct(
        VariableAssignmentCallFactory $variableAssignmentCallFactory,
        TranspilableSourceComposer $transpilableSourceComposer,
        WebDriverElementMutatorCallFactory $webDriverElementMutatorCallFactory
    ) {
        $this->variableAssignmentCallFactory = $variableAssignmentCallFactory;
        $this->transpilableSourceComposer = $transpilableSourceComposer;
        $this->webDriverElementMutatorCallFactory = $webDriverElementMutatorCallFactory;
    }

    public static function createTranspiler(): SetActionTranspiler
    {
        return new SetActionTranspiler(
            VariableAssignmentCallFactory::createFactory(),
            TranspilableSourceComposer::create(),
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
     * @return TranspilableSourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): TranspilableSourceInterface
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

        $variablePlaceholders = new VariablePlaceholderCollection();
        $elementLocatorPlaceholder = $variablePlaceholders->create('ELEMENT_LOCATOR');
        $collectionPlaceholder = $variablePlaceholders->create('COLLECTION');
        $valuePlaceholder = $variablePlaceholders->create('VALUE');

        $collectionAssignmentCall = $this->variableAssignmentCallFactory->createForElementCollection(
            $identifier,
            $elementLocatorPlaceholder,
            $collectionPlaceholder
        );

        $valueAssignmentCall = $this->variableAssignmentCallFactory->createForValue(
            $model->getValue(),
            $valuePlaceholder
        );

        $mutationCall = $this->webDriverElementMutatorCallFactory->createSetValueCall(
            $collectionPlaceholder,
            $valuePlaceholder
        );

        $statements = array_merge(
            $collectionAssignmentCall->getLines(),
            null === $valueAssignmentCall ? [] : $valueAssignmentCall->getLines(),
            $mutationCall->getLines()
        );
        $calls = [
            $collectionAssignmentCall,
            $mutationCall,
        ];

        if ($valueAssignmentCall instanceof VariableAssignmentCall) {
            $calls[] = $valueAssignmentCall;
        }

        return $this->transpilableSourceComposer->compose(
            $statements,
            $calls,
            new UseStatementCollection(),
            $variablePlaceholders
        );
    }
}
