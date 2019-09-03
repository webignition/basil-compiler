<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Identifier;

use webignition\BasilModel\Identifier\AttributeIdentifierInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\SingleQuotedStringEscaper;
use webignition\BasilTranspiler\TranspilationResultComposer;
use webignition\BasilTranspiler\TranspilerInterface;

class AttributeIdentifierTranspiler implements TranspilerInterface
{
    const TEMPLATE = '%s->getAttribute(\'%s\')';

    private $variableAssignmentCallFactory;
    private $singleQuotedStringEscaper;
    private $transpilationResultComposer;

    public function __construct(
        VariableAssignmentCallFactory $variableAssignmentCallFactory,
        SingleQuotedStringEscaper $singleQuotedStringEscaper,
        TranspilationResultComposer $transpilationResultComposer
    ) {
        $this->variableAssignmentCallFactory = $variableAssignmentCallFactory;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
        $this->transpilationResultComposer = $transpilationResultComposer;
    }

    public static function createTranspiler(): AttributeIdentifierTranspiler
    {
        return new AttributeIdentifierTranspiler(
            VariableAssignmentCallFactory::createFactory(),
            SingleQuotedStringEscaper::create(),
            TranspilationResultComposer::create()
        );
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof AttributeIdentifierInterface) {
            return false;
        }

        return '' !== trim((string) $model->getAttributeName());
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
        if (!$model instanceof AttributeIdentifierInterface) {
            throw new NonTranspilableModelException($model);
        }

        $attributeName = trim((string) $model->getAttributeName());
        if ('' === $attributeName) {
            throw new NonTranspilableModelException($model);
        }

        $variablePlaceholders = new VariablePlaceholderCollection();
        $attributePlaceholder = $variablePlaceholders->create('ATTRIBUTE');

        $elementAssignmentCall = $this->variableAssignmentCallFactory->createForElement($model->getElementIdentifier());
        $elementPlaceholder = $elementAssignmentCall->getElementVariablePlaceholder();

        $attributeAssignmentStatement = $attributePlaceholder . ' = ' . sprintf(
            self::TEMPLATE,
            $elementPlaceholder,
            $this->singleQuotedStringEscaper->escape((string) $model->getAttributeName())
        );

        $statements = array_merge($elementAssignmentCall->getLines(), [
            $attributeAssignmentStatement,
        ]);

        $calls = [
            $elementAssignmentCall,
        ];

        return $this->transpilationResultComposer->compose(
            $statements,
            $calls,
            new UseStatementCollection(),
            $variablePlaceholders
        );
    }
}
