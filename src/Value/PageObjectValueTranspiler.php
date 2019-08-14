<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\UnknownObjectPropertyException;

class PageObjectValueTranspiler implements ValueTypeTranspilerInterface
{
    const PROPERTY_NAME_TITLE = 'title';
    const PROPERTY_NAME_URL = 'url';
    const TRANSPILED_TITLE = 'self::$client->getTitle()';
    const TRANSPILED_URL = 'self::$client->getCurrentURL()';

    private $transpiledValueMap = [
        self::PROPERTY_NAME_TITLE => self::TRANSPILED_TITLE,
        self::PROPERTY_NAME_URL => self::TRANSPILED_URL,
    ];

    public static function createTranspiler(): ValueTypeTranspilerInterface
    {
        return new PageObjectValueTranspiler();
    }

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

        $transpiledValue = $this->transpiledValueMap[$value->getObjectProperty()] ?? null;

        if (is_string($transpiledValue)) {
            return $transpiledValue;
        }

        throw new UnknownObjectPropertyException($value);
    }
}
