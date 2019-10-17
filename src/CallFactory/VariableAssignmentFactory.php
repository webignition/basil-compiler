<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class VariableAssignmentFactory
{
    public static function createFactory(): VariableAssignmentFactory
    {
        return new VariableAssignmentFactory();
    }

    public function createForValueAccessor(
        CompilableSourceInterface $accessor,
        VariablePlaceholder $placeholder,
        string $type = 'string',
        string $default = 'null'
    ): CompilableSourceInterface {
        $assignment = clone $accessor;
        $assignment->prependStatement(-1, $placeholder . ' = ');
        $assignment->appendStatement(-1, ' ?? ' . $default);

        $variableExports = new VariablePlaceholderCollection([
            $placeholder,
        ]);

        $assignment = $assignment->withCompilationMetadata(
            $assignment->getCompilationMetadata()->withAdditionalVariableExports($variableExports)
        );

        return (new CompilableSource())
            ->withPredecessors([$assignment])
            ->withStatements([
                sprintf('%s = (%s) %s', (string) $placeholder, $type, (string) $placeholder)
            ]);
    }
}
