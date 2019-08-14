<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\UnknownObjectPropertyException;

class BrowserObjectValueTranspiler implements ValueTypeTranspilerInterface
{
    const PROPERTY_NAME_SIZE = 'size';

    public static function createTranspiler(): ValueTypeTranspilerInterface
    {
        return new BrowserObjectValueTranspiler();
    }

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

    /**
     * @param ValueInterface $value
     *
     * @return string|null
     *
     * @throws UnknownObjectPropertyException
     */
    public function transpile(ValueInterface $value): ?string
    {
        if (!$this->handles($value) || !$value instanceof ObjectValueInterface) {
            return null;
        }

        if (self::PROPERTY_NAME_SIZE === $value->getObjectProperty()) {
            return 'self::$client->getWebDriver()->manage()->window()->getSize()';
        }

        throw new UnknownObjectPropertyException($value);
    }
}
