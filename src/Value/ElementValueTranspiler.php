<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ElementValueInterface;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class ElementValueTranspiler implements TranspilerInterface
{
    private $elementExpressionTranspiler;

    public function __construct(ElementExpressionTranspiler $elementExpressionTranspiler)
    {
        $this->elementExpressionTranspiler = $elementExpressionTranspiler;
    }

    public static function createTranspiler(): ElementValueTranspiler
    {
        return new ElementValueTranspiler(
            ElementExpressionTranspiler::createTranspiler()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof ElementValueInterface;
    }

    public function transpile(object $model): TranspilationResultInterface
    {
        if ($model instanceof ElementValueInterface) {
            $identifier = $model->getIdentifier();
            $elementExpression = $identifier->getElementExpression();

            if ($elementExpression instanceof LiteralValueInterface) {
                return $this->elementExpressionTranspiler->transpile($elementExpression);
            }
        }

        throw new NonTranspilableModelException($model);
    }
}
