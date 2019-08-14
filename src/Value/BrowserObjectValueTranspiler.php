<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;

class BrowserObjectValueTranspiler extends AbstractObjectValueTranspiler implements ValueTypeTranspilerInterface
{
    const PROPERTY_NAME_SIZE = 'size';
    const TRANSPILED_SIZE = 'self::$client->getWebDriver()->manage()->window()->getSize()';

    private $transpiledValueMap = [
        self::PROPERTY_NAME_SIZE => self::TRANSPILED_SIZE,
    ];

    public function handles(ValueInterface $value): bool
    {
        if (!$value instanceof ObjectValueInterface) {
            return false;
        }

        if (ValueTypes::BROWSER_OBJECT_PROPERTY !== $value->getType()) {
            return false;
        }

        return ObjectNames::BROWSER === $value->getObjectName();
    }

    protected function getTranspiledValueMap(): array
    {
        return $this->transpiledValueMap;
    }
}
