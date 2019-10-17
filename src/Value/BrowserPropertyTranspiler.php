<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;
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
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     * @throws UnknownObjectPropertyException
     */
    public function transpile(object $model): SourceInterface
    {
        if (!$this->handles($model) || !$model instanceof ObjectValueInterface) {
            throw new NonTranspilableModelException($model);
        }

        $property = $model->getProperty();
        if (self::PROPERTY_NAME_SIZE !== $property) {
            throw new UnknownObjectPropertyException($model);
        }

        $variableExports = new VariablePlaceholderCollection();
        $webDriverDimensionPlaceholder = $variableExports->create('WEBDRIVER_DIMENSION');

        $variableDependencies = new VariablePlaceholderCollection();
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $dimensionAssignment = (new Source())
            ->withStatements([
                sprintf(
                    '%s = %s->getWebDriver()->manage()->window()->getSize()',
                    $webDriverDimensionPlaceholder,
                    $pantherClientPlaceholder
                ),
            ])
            ->withMetadata(
                (new Metadata())
                ->withVariableDependencies($variableDependencies)
                ->withVariableExports($variableExports)
            );

        $getWidthCall = $webDriverDimensionPlaceholder . '->getWidth()';
        $getHeightCall = $webDriverDimensionPlaceholder . '->getHeight()';

        $dimensionConcatenation = (new Source())
            ->withStatements([
                '(string) ' . $getWidthCall . ' . \'x\' . (string) ' . $getHeightCall,
            ]);

        return (new Source())
            ->withPredecessors([$dimensionAssignment, $dimensionConcatenation]);
    }
}
