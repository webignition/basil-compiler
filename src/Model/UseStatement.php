<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class UseStatement
{
    private $className;
    private $alias;

    public function __construct(string $className, ?string $alias = null)
    {
        $this->className = $className;
        $this->alias = $alias;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }
}
