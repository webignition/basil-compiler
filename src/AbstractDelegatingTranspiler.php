<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilTranspiler\Model\TranspilationResult;

abstract class AbstractDelegatingTranspiler implements TranspilerInterface
{
    /**
     * @var TranspilerInterface[]
     */
    private $delegatedTranspilers = [];

    public function __construct(array $delegatedTranspilers = [])
    {
        foreach ($delegatedTranspilers as $delegatedTranspiler) {
            if ($delegatedTranspiler instanceof TranspilerInterface) {
                $this->addDelegatedTranspiler($delegatedTranspiler);
            }
        }
    }

    public function addDelegatedTranspiler(TranspilerInterface $transpiler)
    {
        $this->delegatedTranspilers[] = $transpiler;
    }

    /**
     * @param object $model
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): TranspilationResult
    {
        $delegatedTranspiler = $this->findDelegatedTranspiler($model);

        if ($delegatedTranspiler instanceof TranspilerInterface) {
            return $delegatedTranspiler->transpile($model);
        }

        throw new NonTranspilableModelException($model);
    }

    protected function findDelegatedTranspiler(object $model): ?TranspilerInterface
    {
        foreach ($this->delegatedTranspilers as $transpiler) {
            if ($transpiler->handles($model)) {
                return $transpiler;
            }
        }

        return null;
    }
}
