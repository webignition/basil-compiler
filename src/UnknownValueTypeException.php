<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilModel\Value\ValueInterface;

class UnknownValueTypeException extends \Exception
{
    private $value;

    public function __construct(ValueInterface $value)
    {
        parent::__construct('Unknown value type "' . $value->getType() . '"');

        $this->value = $value;
    }

    public function getValue(): ValueInterface
    {
        return $this->value;
    }
}
