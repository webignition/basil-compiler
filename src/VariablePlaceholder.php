<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

class VariablePlaceholder
{
    const TEMPLATE = '{{ %s }}';

    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return sprintf(self::TEMPLATE, $this->name);
    }
}
