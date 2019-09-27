<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilModel\Action\InputActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\CallFactory\WebDriverElementMutatorCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilationResultComposer;
use webignition\BasilTranspiler\TranspilerInterface;

class SetActionTranspiler implements TranspilerInterface
{
    private $variableAssignmentCallFactory;
    private $transpilationResultComposer;
    private $webDriverElementMutatorCallFactory;

    public function __construct(
        VariableAssignmentCallFactory $variableAssignmentCallFactory,
        TranspilationResultComposer $transpilationResultComposer,
        WebDriverElementMutatorCallFactory $webDriverElementMutatorCallFactory
    ) {
        $this->variableAssignmentCallFactory = $variableAssignmentCallFactory;
        $this->transpilationResultComposer = $transpilationResultComposer;
        $this->webDriverElementMutatorCallFactory = $webDriverElementMutatorCallFactory;
    }

    public static function createTranspiler(): SetActionTranspiler
    {
        return new SetActionTranspiler(
            VariableAssignmentCallFactory::createFactory(),
            TranspilationResultComposer::create(),
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
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): TranspilationResultInterface
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
            $valueAssignmentCall->getLines(),
            $mutationCall->getLines()
        );

        $calls = [
            $collectionAssignmentCall,
            $valueAssignmentCall,
            $mutationCall,
        ];

        return $this->transpilationResultComposer->compose(
            $statements,
            $calls,
            new UseStatementCollection(),
            $variablePlaceholders
        );
    }
}
