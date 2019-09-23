<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilModel\Action\WaitActionInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilationResultComposer;
use webignition\BasilTranspiler\TranspilerInterface;

class WaitActionTranspiler implements TranspilerInterface
{
    const DURATION_PLACEHOLDER = 'DURATION';
    const MICROSECONDS_PER_MILLISECOND = 1000;

    private $variableAssignmentCallFactory;
    private $transpilationResultComposer;

    public function __construct(
        VariableAssignmentCallFactory $variableAssignmentCallFactory,
        TranspilationResultComposer $transpilationResultComposer
    ) {
        $this->variableAssignmentCallFactory = $variableAssignmentCallFactory;
        $this->transpilationResultComposer = $transpilationResultComposer;
    }

    public static function createTranspiler(): WaitActionTranspiler
    {
        return new WaitActionTranspiler(
            VariableAssignmentCallFactory::createFactory(),
            TranspilationResultComposer::create()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof WaitActionInterface;
    }

    /**
     * @param object $model
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): TranspilationResultInterface
    {
        if (!$model instanceof WaitActionInterface) {
            throw new NonTranspilableModelException($model);
        }

        $variablePlaceholders = new VariablePlaceholderCollection();
        $durationPlaceholder = $variablePlaceholders->create(self::DURATION_PLACEHOLDER);

        $duration = $model->getDuration();

        $durationAssignmentCall = $this->variableAssignmentCallFactory->createIntegerValueVariableAssignmentCall(
            $duration,
            $durationPlaceholder
        );

        if (null === $durationAssignmentCall) {
            throw new NonTranspilableModelException($model);
        }

        $waitStatement = sprintf(
            'usleep(%s * %s)',
            (string) $durationPlaceholder,
            self::MICROSECONDS_PER_MILLISECOND
        );

        return $this->transpilationResultComposer->compose(
            array_merge($durationAssignmentCall->getLines(), [
                $waitStatement
            ]),
            [
                $durationAssignmentCall
            ],
            new UseStatementCollection(),
            $variablePlaceholders
        );
    }
}
