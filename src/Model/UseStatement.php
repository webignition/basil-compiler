<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class UseStatement implements UniqueItemInterface
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

    public function getId(): string
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        $string = $this->className;

        if (null !== $this->alias) {
            $string .= ' as ' . $this->alias;
        }

        return $string;
    }
}
