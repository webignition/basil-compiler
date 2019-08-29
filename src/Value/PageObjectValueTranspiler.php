<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNames;

class PageObjectValueTranspiler extends AbstractObjectValueTranspiler implements TranspilerInterface
{
    const PROPERTY_NAME_TITLE = 'title';
    const PROPERTY_NAME_URL = 'url';

    private $variablePlaceholders;

    public function __construct()
    {
        $this->variablePlaceholders = VariablePlaceholderCollection::createCollection([
            VariableNames::PANTHER_CLIENT,
        ]);
    }

    public static function createTranspiler(): PageObjectValueTranspiler
    {
        return new PageObjectValueTranspiler();
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof ObjectValueInterface) {
            return false;
        }

        if (ValueTypes::PAGE_OBJECT_PROPERTY !== $model->getType()) {
            return false;
        }

        return ObjectNames::PAGE === $model->getObjectName();
    }

    protected function getTranspiledValueMap(): array
    {
        $pantherClientPlaceholder = $this->variablePlaceholders->get(VariableNames::PANTHER_CLIENT);

        return [
            self::PROPERTY_NAME_TITLE => (string) $pantherClientPlaceholder . '->getTitle()',
            self::PROPERTY_NAME_URL => (string) $pantherClientPlaceholder . '->getCurrentURL()',
        ];
    }

    protected function getVariablePlaceholders(): VariablePlaceholderCollection
    {
        return $this->variablePlaceholders;
    }
}
