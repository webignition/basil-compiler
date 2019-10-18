<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\WaitActionInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentFactory;
use webignition\BasilTranspiler\Model\NamedDomIdentifierValue;
use webignition\BasilTranspiler\NamedDomIdentifierTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\Value\ValueTranspiler;

class WaitActionTranspiler implements TranspilerInterface
{
    const DURATION_PLACEHOLDER = 'DURATION';
    const MICROSECONDS_PER_MILLISECOND = 1000;

    private $variableAssignmentFactory;
    private $valueTranspiler;
    private $namedDomIdentifierTranspiler;

    public function __construct(
        VariableAssignmentFactory $variableAssignmentFactory,
        ValueTranspiler $valueTranspiler,
        NamedDomIdentifierTranspiler $namedDomIdentifierTranspiler
    ) {
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->valueTranspiler = $valueTranspiler;
        $this->namedDomIdentifierTranspiler = $namedDomIdentifierTranspiler;
    }

    public static function createTranspiler(): WaitActionTranspiler
    {
        return new WaitActionTranspiler(
            VariableAssignmentFactory::createFactory(),
            ValueTranspiler::createTranspiler(),
            NamedDomIdentifierTranspiler::createTranspiler()
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
            $durationAccessor = $this->namedDomIdentifierTranspiler->transpile(
                new NamedDomIdentifierValue($duration, $durationPlaceholder)
            );
        } else {
            $durationAccessor = $this->valueTranspiler->transpile($duration);
        }

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
