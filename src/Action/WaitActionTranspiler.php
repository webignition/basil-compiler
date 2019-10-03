<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\WaitActionInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class WaitActionTranspiler implements TranspilerInterface
{
    const DURATION_PLACEHOLDER = 'DURATION';
    const MICROSECONDS_PER_MILLISECOND = 1000;

    private $variableAssignmentCallFactory;

    public function __construct(VariableAssignmentCallFactory $variableAssignmentCallFactory)
    {
        $this->variableAssignmentCallFactory = $variableAssignmentCallFactory;
    }

    public static function createTranspiler(): WaitActionTranspiler
    {
        return new WaitActionTranspiler(
            VariableAssignmentCallFactory::createFactory()
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

        $compilationMetadata = (new CompilationMetadata())
            ->merge([$durationAssignmentCall->getCompilationMetadata()])
            ->withAdditionalVariableExports($variableExports);

        $compilableSource = new CompilableSource(
            array_merge($durationAssignmentCall->getStatements(), [
                $waitStatement
            ]),
            $compilationMetadata
        );

        return $compilableSource;
    }
}
