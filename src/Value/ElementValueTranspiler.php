<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ElementValueInterface;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class ElementValueTranspiler implements TranspilerInterface
{
    private $literalValueTranspiler;

    public function __construct(LiteralValueTranspiler $literalValueTranspiler)
    {
        $this->literalValueTranspiler = $literalValueTranspiler;
    }

    public static function createTranspiler(): ElementValueTranspiler
    {
        return new ElementValueTranspiler(
            LiteralValueTranspiler::createTranspiler()
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
            $identifierValue = $identifier->getValue();

            if ($identifierValue instanceof LiteralValueInterface) {
                return $this->literalValueTranspiler->transpile($identifierValue);
            }
        }

        throw new NonTranspilableModelException($model);
    }
}
