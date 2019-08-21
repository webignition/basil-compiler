<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

class NonTranspilableModelException extends \Exception
{
    private $model;

    public function __construct(object $model)
    {
        parent::__construct('Non-transpilable model "' . get_class($model) . '"');

        $this->model = $model;
    }

    public function getModel(): object
    {
        return $this->model;
    }
}
