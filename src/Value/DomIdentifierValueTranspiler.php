<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\DomIdentifierValueInterface;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class DomIdentifierValueTranspiler implements TranspilerInterface
{
    private $elementExpressionTranspiler;

    public function __construct(ElementExpressionTranspiler $elementExpressionTranspiler)
    {
        $this->elementExpressionTranspiler = $elementExpressionTranspiler;
    }

    public static function createTranspiler(): DomIdentifierValueTranspiler
    {
        return new DomIdentifierValueTranspiler(
            ElementExpressionTranspiler::createTranspiler()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof DomIdentifierValueInterface;
    }

    public function transpile(object $model): TranspilationResultInterface
    {
        if ($model instanceof DomIdentifierValueInterface) {
            $identifier = $model->getIdentifier();
            $elementExpression = $identifier->getElementExpression();

            return $this->elementExpressionTranspiler->transpile($elementExpression);
        }

        throw new NonTranspilableModelException($model);
    }
}
