<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class VariableAssignment extends CompilableSource
{
    const STATEMENT_PATTERN = '%s = %s';

    private $variablePlaceholder;

    public function __construct(VariablePlaceholder $variablePlaceholder)
    {
        parent::__construct();

        $this->variablePlaceholder = $variablePlaceholder;
        $this->setPlaceholderAsVariableExport();
    }

    public static function fromCompilableSource(
        CompilableSourceInterface $source,
        VariablePlaceholder $variablePlaceholder
    ): CompilableSourceInterface {
        $variableAssignment = new VariableAssignment($variablePlaceholder);

        $variableAssignment = $variableAssignment->withPredecessors($source->getPredecessors());
        $variableAssignment = $variableAssignment->withStatements($source->getStatements());
        $variableAssignment = $variableAssignment->withCompilationMetadata($source->getCompilationMetadata());

        return $variableAssignment;
    }

    public function getVariablePlaceholder(): VariablePlaceholder
    {
        return $this->variablePlaceholder;
    }

    public function getStatements(): array
    {
        $statements = parent::getStatements();
        $finalStatement = array_pop($statements);

        $finalStatement = sprintf(self::STATEMENT_PATTERN, $this->variablePlaceholder, $finalStatement);

        $statements[] = $finalStatement;

        return $statements;
    }

    public function withCompilationMetadata(
        CompilationMetadataInterface $compilationMetadata
    ): CompilableSourceInterface {
        $new = parent::withCompilationMetadata($compilationMetadata);

        if ($new instanceof VariableAssignment) {
            $new = $new->setPlaceholderAsVariableExport();
        }

        return $new;
    }

    private function setPlaceholderAsVariableExport()
    {
        $compilationMetadata = $this->getCompilationMetadata();
        $compilationMetadata = $compilationMetadata->withAdditionalVariableExports(new VariablePlaceholderCollection([
            $this->variablePlaceholder,
        ]));

        return parent::withCompilationMetadata($compilationMetadata);
    }
}
