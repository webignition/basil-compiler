<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNames;

class BrowserObjectValueTranspiler extends AbstractObjectValueTranspiler implements TranspilerInterface
{
    const PROPERTY_NAME_SIZE = 'size';
    const TRANSPILED_SIZE =
        '{{ ' . VariableNames::PANTHER_CLIENT . ' }}->getWebDriver()->manage()->window()->getSize()';

    private $transpiledValueMap = [
        self::PROPERTY_NAME_SIZE => self::TRANSPILED_SIZE,
    ];

    public static function createTranspiler(): BrowserObjectValueTranspiler
    {
        return new BrowserObjectValueTranspiler();
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof ObjectValueInterface) {
            return false;
        }

        if (ValueTypes::BROWSER_OBJECT_PROPERTY !== $model->getType()) {
            return false;
        }

        return ObjectNames::BROWSER === $model->getObjectName();
    }

    protected function getTranspiledValueMap(array $variableIdentifiers = []): array
    {
        return $this->transpiledValueMap;
    }
}
