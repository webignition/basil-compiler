<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\UnknownValueTypeException;

class ValueTranspiler
{
    /**
     * @var ValueTypeTranspilerInterface[]
     */
    private $valueTypeTranspilers = [];

    public function __construct(array $valueTypeTranspilers = [])
    {
        foreach ($valueTypeTranspilers as $valueTypeTranspiler) {
            if ($valueTypeTranspiler instanceof ValueTypeTranspilerInterface) {
                $this->addValueTypeTranspiler($valueTypeTranspiler);
            }
        }
    }

    public static function createTranspiler(): ValueTranspiler
    {
        return new ValueTranspiler([
            LiteralStringValueTranspiler::createTranspiler(),
        ]);
    }

    public function addValueTypeTranspiler(ValueTypeTranspilerInterface $valueTypeTranspiler)
    {
        $this->valueTypeTranspilers[] = $valueTypeTranspiler;
    }

    /**
     * @param ValueInterface $value
     *
     * @return string
     *
     * @throws UnknownValueTypeException
     */
    public function transpile(ValueInterface $value): string
    {
        $valueTypeTranspiler = $this->findValueTypeTranspiler($value);

        if ($valueTypeTranspiler instanceof ValueTypeTranspilerInterface) {
            return $valueTypeTranspiler->transpile($value);
        }

        throw new UnknownValueTypeException($value);
    }

    private function findValueTypeTranspiler(ValueInterface $value): ?ValueTypeTranspilerInterface
    {
        foreach ($this->valueTypeTranspilers as $valueTypeTranspiler) {
            if ($valueTypeTranspiler->handles($value)) {
                return $valueTypeTranspiler;
            }
        }

        return null;
    }
}
