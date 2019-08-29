<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class UseStatement implements UniqueItemInterface
{
    const STRING_TEMPLATE = '%s as %s';

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
        return md5((string) $this);
    }

    public function __toString(): string
    {
        return sprintf(self::STRING_TEMPLATE, $this->className, $this->alias);
    }
}
