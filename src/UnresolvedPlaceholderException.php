<?php

declare(strict_types=1);

namespace webignition\BasilCompiler;

class UnresolvedPlaceholderException extends \Exception
{
    private $placeholder;
    private $content;

    public function __construct(string $placeholder, string $content)
    {
        parent::__construct(sprintf('Unresolved placeholder "%s" in content "%s"', $placeholder, $content));

        $this->placeholder = $placeholder;
        $this->content = $content;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
