<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\WaitActionInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentFactory;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\NonTranspilableValueException;
use webignition\BasilTranspiler\TranspilerInterface;

class WaitActionTranspiler implements TranspilerInterface
{
    const DURATION_PLACEHOLDER = 'DURATION';
    const MICROSECONDS_PER_MILLISECOND = 1000;

    private $variableAssignmentFactory;

    public function __construct(VariableAssignmentFactory $variableAssignmentFactory)
    {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
    }

    public static function createTranspiler(): WaitActionTranspiler
    {
        return new WaitActionTranspiler(
            VariableAssignmentFactory::createFactory()
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

        $variableExports = new VariablePlaceholderCollection();
        $durationPlaceholder = $variableExports->create(self::DURATION_PLACEHOLDER);

        $duration = $model->getDuration();

        try {
            $durationAssignment = $this->variableAssignmentFactory->createForValue(
                $duration,
                $durationPlaceholder,
                'int',
                '0'
            );
        } catch (NonTranspilableValueException $nonTranspilableValueException) {
            throw new NonTranspilableModelException($model);
        }

        $waitStatement = sprintf(
            'usleep(%s * %s)',
            (string) $durationPlaceholder,
            self::MICROSECONDS_PER_MILLISECOND
        );

        return (new CompilableSource())
            ->withPredecessors([$durationAssignment])
            ->withStatements([$waitStatement]);
    }
}
