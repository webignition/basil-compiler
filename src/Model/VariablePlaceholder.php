<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class VariablePlaceholder implements UniqueItemInterface
{
    const TEMPLATE = '{{ %s }}';

    private $name = '';

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return sprintf(self::TEMPLATE, $this->name);
    }
}
