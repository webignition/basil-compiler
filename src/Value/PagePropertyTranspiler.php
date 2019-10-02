<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\UnknownObjectPropertyException;
use webignition\BasilTranspiler\VariableNames;

class PagePropertyTranspiler implements TranspilerInterface
{
    const PROPERTY_NAME_TITLE = 'title';
    const PROPERTY_NAME_URL = 'url';

    private $variablePlaceholders;
    private $transpiledValueMap;

    public function __construct()
    {
        $this->variablePlaceholders = new VariablePlaceholderCollection();
        $pantherClientVariablePlaceholder = $this->variablePlaceholders->create(VariableNames::PANTHER_CLIENT);
        $pantherClientPlaceholderAsString = (string) $pantherClientVariablePlaceholder;

        $this->transpiledValueMap = [
            self::PROPERTY_NAME_TITLE => $pantherClientPlaceholderAsString . '->getTitle()',
            self::PROPERTY_NAME_URL => $pantherClientPlaceholderAsString . '->getCurrentURL()',
        ];
    }

    public static function createTranspiler(): PagePropertyTranspiler
    {
        return new PagePropertyTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof ObjectValueInterface && ObjectValueType::PAGE_PROPERTY === $model->getType();
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
        if ($this->handles($model) && $model instanceof ObjectValueInterface) {
            $transpiledValue = $this->transpiledValueMap[$model->getProperty()] ?? null;

            if (is_string($transpiledValue)) {
                return new CompilableSource(
                    [$transpiledValue],
                    new ClassDependencyCollection(),
                    $this->variablePlaceholders,
                    new VariablePlaceholderCollection()
                );
            }

            throw new UnknownObjectPropertyException($model);
        }

        throw new NonTranspilableModelException($model);
    }
}
