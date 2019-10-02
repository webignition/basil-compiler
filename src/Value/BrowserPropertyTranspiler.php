<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\UnknownObjectPropertyException;
use webignition\BasilTranspiler\VariableNames;

class BrowserPropertyTranspiler implements TranspilerInterface
{
    const PROPERTY_NAME_SIZE = 'size';

    public static function createTranspiler(): BrowserPropertyTranspiler
    {
        return new BrowserPropertyTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof ObjectValueInterface && ObjectValueType::BROWSER_PROPERTY === $model->getType();
    }

    /**
     * @param object $model
     *
     * @return CompilableSourceInterface
     *
     * @throws NonTranspilableModelException
     * @throws UnknownObjectPropertyException
     */
    public function transpile(object $model): CompilableSourceInterface
    {
        if (!$this->handles($model) || !$model instanceof ObjectValueInterface) {
            throw new NonTranspilableModelException($model);
        }

        $property = $model->getProperty();
        if (self::PROPERTY_NAME_SIZE !== $property) {
            throw new UnknownObjectPropertyException($model);
        }

        $variablePlaceholders = new VariablePlaceholderCollection();
        $webDriverDimensionPlaceholder = $variablePlaceholders->create('WEBDRIVER_DIMENSION');
        $valuePlaceholder = $variablePlaceholders->create('BROWSER_SIZE');

        $variableDependencies = new VariablePlaceholderCollection();
        $pantherClientDependency = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $dimensionAssignmentStatement = sprintf(
            '%s = %s',
            $webDriverDimensionPlaceholder,
            $pantherClientDependency . '->getWebDriver()->manage()->window()->getSize()'
        );

        $getWidthCall = $webDriverDimensionPlaceholder . '->getWidth()';
        $getHeightCall = $webDriverDimensionPlaceholder . '->getHeight()';

        $dimensionConcatenationStatement = '(string) ' . $getWidthCall . ' . \'x\' . (string) ' . $getHeightCall;

        return new VariableAssignmentCall(
            new CompilableSource(
                [
                    $dimensionAssignmentStatement,
                    $dimensionConcatenationStatement,
                ],
                new ClassDependencyCollection(),
                $variablePlaceholders,
                $variableDependencies
            ),
            $valuePlaceholder
        );
    }
}
