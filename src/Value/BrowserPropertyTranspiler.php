<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\BrowserProperty;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\UnknownObjectPropertyException;
use webignition\BasilTranspiler\VariableNames;

class BrowserPropertyTranspiler implements TranspilerInterface
{
    const PROPERTY_NAME_SIZE = 'size';

    private $variablePlaceholders;

    public function __construct()
    {
        $this->variablePlaceholders = VariablePlaceholderCollection::createCollection([
            VariableNames::PANTHER_CLIENT,
        ]);
    }

    public static function createTranspiler(): BrowserPropertyTranspiler
    {
        return new BrowserPropertyTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof BrowserProperty;
    }

    /**
     * @param object $model
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     * @throws UnknownObjectPropertyException
     */
    public function transpile(object $model): TranspilationResultInterface
    {
        if (!$this->handles($model) || !$model instanceof BrowserProperty) {
            throw new NonTranspilableModelException($model);
        }

        $property = $model->getProperty();
        if (self::PROPERTY_NAME_SIZE !== $property) {
            throw new UnknownObjectPropertyException($model);
        }

        $variablePlaceholders = new VariablePlaceholderCollection();
        $webDriverDimensionPlaceholder = $variablePlaceholders->create('WEBDRIVER_DIMENSION');
        $valuePlaceholder = $variablePlaceholders->create('BROWSER_SIZE');
        $pantherClientPlaceholder = $variablePlaceholders->create(VariableNames::PANTHER_CLIENT);

        $dimensionAssignmentStatement = sprintf(
            '%s = %s',
            $webDriverDimensionPlaceholder,
            $pantherClientPlaceholder . '->getWebDriver()->manage()->window()->getSize()'
        );

        $getWidthCall = $webDriverDimensionPlaceholder . '->getWidth()';
        $getHeightCall = $webDriverDimensionPlaceholder . '->getHeight()';

        $dimensionConcatenationStatement = '(string) ' . $getWidthCall . ' . \'x\' . (string) ' . $getHeightCall;

        return new VariableAssignmentCall(
            new TranspilationResult(
                [
                    $dimensionAssignmentStatement,
                    $dimensionConcatenationStatement,
                ],
                new UseStatementCollection(),
                new VariablePlaceholderCollection([
                    $webDriverDimensionPlaceholder,
                    $valuePlaceholder,
                    $pantherClientPlaceholder,
                ])
            ),
            $valuePlaceholder
        );
    }
}