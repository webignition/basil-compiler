<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Value\AttributeValueInterface;
use webignition\BasilModel\Value\ElementValueInterface;
use webignition\BasilModel\Value\EnvironmentValueInterface;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueTypes;

class AssertableValueExaminer
{
    public static function create(): AssertableValueExaminer
    {
        return new AssertableValueExaminer();
    }

    public function isAssertableExaminedValue(?object $value = null): bool
    {
        if ($value instanceof ElementValueInterface) {
            return true;
        }

        if ($value instanceof AttributeValueInterface) {
            return true;
        }

        if ($value instanceof EnvironmentValueInterface) {
            return true;
        }

        if ($value instanceof ObjectValueInterface) {
            if (in_array($value->getType(), [ValueTypes::BROWSER_OBJECT_PROPERTY, ValueTypes::PAGE_OBJECT_PROPERTY])) {
                return true;
            }
        }

        return false;
    }
}
