<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilModel\Action\WaitActionInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilableSourceComposer;
use webignition\BasilTranspiler\TranspilerInterface;

class WaitActionTranspiler implements TranspilerInterface
{
    const DURATION_PLACEHOLDER = 'DURATION';
    const MICROSECONDS_PER_MILLISECOND = 1000;

    private $variableAssignmentCallFactory;
    private $transpilableSourceComposer;

    public function __construct(
        VariableAssignmentCallFactory $variableAssignmentCallFactory,
        TranspilableSourceComposer $transpilableSourceComposer
    ) {
        $this->variableAssignmentCallFactory = $variableAssignmentCallFactory;
        $this->transpilableSourceComposer = $transpilableSourceComposer;
    }

    public static function createTranspiler(): WaitActionTranspiler
    {
        return new WaitActionTranspiler(
            VariableAssignmentCallFactory::createFactory(),
            TranspilableSourceComposer::create()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof WaitActionInterface;
    }

    /**
     * @param object $model
     *
     * @return CompilableSourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): CompilableSourceInterface
    {
        if (!$model instanceof WaitActionInterface) {
            throw new NonTranspilableModelException($model);
        }

        $variablePlaceholders = new VariablePlaceholderCollection();
        $durationPlaceholder = $variablePlaceholders->create(self::DURATION_PLACEHOLDER);

        $duration = $model->getDuration();

        $durationAssignmentCall = $this->variableAssignmentCallFactory->createForValue(
            $duration,
            $durationPlaceholder,
            'int',
            '0'
        );

        if (null === $durationAssignmentCall) {
            throw new NonTranspilableModelException($model);
        }

        $waitStatement = sprintf(
            'usleep(%s * %s)',
            (string) $durationPlaceholder,
            self::MICROSECONDS_PER_MILLISECOND
        );

        return $this->transpilableSourceComposer->compose(
            array_merge($durationAssignmentCall->getStatements(), [
                $waitStatement
            ]),
            [
                $durationAssignmentCall
            ],
            new ClassDependencyCollection(),
            $variablePlaceholders
        );
    }
}
