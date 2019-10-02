<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class ClassDependency implements UniqueItemInterface
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
        $id = $this->className;

        if (null !== $this->alias) {
            $id .= ':' . $this->alias;
        }

        return $id;
    }
}
