<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueInterface;

class UnknownObjectPropertyException extends \Exception
{
    private $value;

    public function __construct(ObjectValueInterface $value)
    {
        parent::__construct('Unknown object property "' . $value->getProperty() . '"');

        $this->value = $value;
    }

    public function getValue(): ValueInterface
    {
        return $this->value;
    }
}
