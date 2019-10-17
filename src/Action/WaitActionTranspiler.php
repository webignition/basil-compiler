<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\WaitActionInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentFactory;
use webignition\BasilTranspiler\Model\NamedDomIdentifierValue;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\Value\ValueTranspiler;

class WaitActionTranspiler implements TranspilerInterface
{
    const DURATION_PLACEHOLDER = 'DURATION';
    const MICROSECONDS_PER_MILLISECOND = 1000;

    private $variableAssignmentFactory;
    private $valueTranspiler;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        ValueTranspiler $valueTranspiler
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->valueTranspiler = $valueTranspiler;
    }

    public static function createTranspiler(): WaitActionTranspiler
    {
        return new WaitActionTranspiler(
            VariableAssignmentFactory::createFactory(),
            ValueTranspiler::createTranspiler()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof WaitActionInterface;
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): SourceInterface
    {
        if (!$model instanceof WaitActionInterface) {
            throw new NonTranspilableModelException($model);
        }

        $variableExports = new VariablePlaceholderCollection();
        $durationPlaceholder = $variableExports->create(self::DURATION_PLACEHOLDER);

        $duration = $model->getDuration();

        if ($duration instanceof DomIdentifierValueInterface) {
            $duration = new NamedDomIdentifierValue($duration, $durationPlaceholder);
        }

        $durationAccessor = $this->valueTranspiler->transpile($duration);

        $durationAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $durationAccessor,
            $durationPlaceholder,
            'int',
            '0'
        );

        $waitStatement = sprintf(
            'usleep(%s * %s)',
            (string) $durationPlaceholder,
            self::MICROSECONDS_PER_MILLISECOND
        );

        return (new Source())
            ->withPredecessors([$durationAssignment])
            ->withStatements([$waitStatement]);
    }
}
