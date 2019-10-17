<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class VariableAssignmentFactory
{
    public static function createFactory(): VariableAssignmentFactory
    {
        return new VariableAssignmentFactory();
    }

    public function createForValueAccessor(
        SourceInterface $accessor,
        VariablePlaceholder $placeholder,
        string $type = 'string',
        string $default = 'null'
    ): SourceInterface {
        $assignment = clone $accessor;
        $assignment->prependStatement(-1, $placeholder . ' = ');
        $assignment->appendStatement(-1, ' ?? ' . $default);

        $variableExports = new VariablePlaceholderCollection([
            $placeholder,
        ]);

        $assignment = $assignment->withMetadata(
            $assignment->getMetadata()->withAdditionalVariableExports($variableExports)
        );

        return (new Source())
            ->withPredecessors([$assignment])
            ->withStatements([
                sprintf('%s = (%s) %s', (string) $placeholder, $type, (string) $placeholder)
            ]);
    }
}
