<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InteractionActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilModel\Value\ElementExpressionType;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\SingleQuotedStringEscaper;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNames;

class WaitForActionTranspiler implements TranspilerInterface
{
    private $singleQuotedStringEscaper;

    public function __construct(SingleQuotedStringEscaper $singleQuotedStringEscaper)
    {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createTranspiler(): WaitForActionTranspiler
    {
        return new WaitForActionTranspiler(SingleQuotedStringEscaper::create());
    }

    public function handles(object $model): bool
    {
        return $model instanceof InteractionActionInterface && ActionTypes::WAIT_FOR === $model->getType();
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
        if (!$model instanceof InteractionActionInterface) {
            throw new NonTranspilableModelException($model);
        }

        if (ActionTypes::WAIT_FOR !== $model->getType()) {
            throw new NonTranspilableModelException($model);
        }

        $identifier = $model->getIdentifier();

        if (!$identifier instanceof DomIdentifierInterface) {
            throw new NonTranspilableModelException($model);
        }

        $elementExpression = $identifier->getElementExpression();

        if (ElementExpressionType::CSS_SELECTOR !== $elementExpression->getType()) {
            throw new NonTranspilableModelException($model);
        }

        $variablePlaceholders = new VariablePlaceholderCollection();
        $pantherCrawlerPlaceholder = $variablePlaceholders->create(VariableNames::PANTHER_CRAWLER);
        $pantherClientPlaceholder = $variablePlaceholders->create(VariableNames::PANTHER_CLIENT);

        return new TranspilationResult(
            [
                sprintf(
                    '%s = %s->waitFor(\'%s\')',
                    $pantherCrawlerPlaceholder,
                    $pantherClientPlaceholder,
                    $this->singleQuotedStringEscaper->escape($elementExpression->getExpression())
                ),
            ],
            new UseStatementCollection(),
            $variablePlaceholders
        );
    }
}
