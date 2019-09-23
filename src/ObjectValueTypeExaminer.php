<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueInterface;

class ObjectValueTypeExaminer
{
    public static function createExaminer(): ObjectValueTypeExaminer
    {
        return new ObjectValueTypeExaminer();
    }

    public function isOfType(ValueInterface $value, array $types): bool
    {
        if (!$value instanceof ObjectValueInterface) {
            return false;
        }

        $valueType = $value->getType();

        foreach ($types as $type) {
            if ($type === $valueType) {
                return true;
            }
        }

        return false;
    }
}
