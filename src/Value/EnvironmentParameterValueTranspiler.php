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
use webignition\BasilTranspiler\VariableNames;

class EnvironmentParameterValueTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): EnvironmentParameterValueTranspiler
    {
        return new EnvironmentParameterValueTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof ObjectValueInterface && ObjectValueType::ENVIRONMENT_PARAMETER === $model->getType();
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): SourceInterface
    {
        if ($this->handles($model) && $model instanceof ObjectValueInterface) {
            $variableDependencies = new VariablePlaceholderCollection();
            $environmentVariableArrayPlaceholder = $variableDependencies->create(
                VariableNames::ENVIRONMENT_VARIABLE_ARRAY
            );

            $statement = sprintf(
                (string) $environmentVariableArrayPlaceholder . '[\'%s\']',
                $model->getProperty()
            );

            $metadata = (new Metadata())->withVariableDependencies($variableDependencies);

            return (new Source())
                ->withStatements([$statement])
                ->withMetadata($metadata);
        }

        throw new NonTranspilableModelException($model);
    }
}
