<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilModel\Value\ValueInterface;

class NonTranspilableValueException extends \Exception
{
    private $value;

    public function __construct(ValueInterface $value)
    {
        parent::__construct('Non-transpilable value "' . get_class($value) . '"');

        $this->value = $value;
    }

    public function getValue(): object
    {
        return $this->value;
    }
}
