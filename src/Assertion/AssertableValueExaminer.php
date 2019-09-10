<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Value\AttributeValueInterface;
use webignition\BasilModel\Value\BrowserProperty;
use webignition\BasilModel\Value\ElementValueInterface;
use webignition\BasilModel\Value\EnvironmentValueInterface;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilModel\Value\PageProperty;

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

        if ($value instanceof BrowserProperty) {
            return true;
        }

        if ($value instanceof PageProperty) {
            return true;
        }

        return false;
    }

    public function isAssertableExpectedValue(?object $value = null): bool
    {
        if ($value instanceof LiteralValueInterface) {
            return true;
        }

        return $this->isAssertableExaminedValue($value);
    }
}
