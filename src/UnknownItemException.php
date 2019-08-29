<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

class UnknownItemException extends \Exception
{
    private $id;

    public function __construct(string $id)
    {
        parent::__construct('Unknown item "' . $id . '"');

        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
