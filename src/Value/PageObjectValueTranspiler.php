<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNames;

class PageObjectValueTranspiler extends AbstractObjectValueTranspiler implements TranspilerInterface
{
    const PROPERTY_NAME_TITLE = 'title';
    const PROPERTY_NAME_URL = 'url';
    const TRANSPILED_TITLE = '{{ ' . VariableNames::PANTHER_CLIENT . ' }}->getTitle()';
    const TRANSPILED_URL = '{{ ' . VariableNames::PANTHER_CLIENT . ' }}->getCurrentURL()';

    private $transpiledValueMap = [
        self::PROPERTY_NAME_TITLE => self::TRANSPILED_TITLE,
        self::PROPERTY_NAME_URL => self::TRANSPILED_URL,
    ];

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

    protected function getTranspiledValueMap(array $variableIdentifiers = []): array
    {
        return $this->transpiledValueMap;
    }
}