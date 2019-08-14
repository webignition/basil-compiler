<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;

class PageObjectValueTranspiler extends AbstractObjectValueTranspiler implements ValueTypeTranspilerInterface
{
    const PROPERTY_NAME_TITLE = 'title';
    const PROPERTY_NAME_URL = 'url';
    const TRANSPILED_TITLE = 'self::$client->getTitle()';
    const TRANSPILED_URL = 'self::$client->getCurrentURL()';

    private $transpiledValueMap = [
        self::PROPERTY_NAME_TITLE => self::TRANSPILED_TITLE,
        self::PROPERTY_NAME_URL => self::TRANSPILED_URL,
    ];

    public function handles(ValueInterface $value): bool
    {
        if (!$value instanceof ObjectValueInterface) {
            return false;
        }

        if (ValueTypes::PAGE_OBJECT_PROPERTY !== $value->getType()) {
            return false;
        }

        return ObjectNames::PAGE === $value->getObjectName();
    }

    protected function getTranspiledValueMap(): array
    {
        return $this->transpiledValueMap;
    }
}
