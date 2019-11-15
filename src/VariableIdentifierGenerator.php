<?php

namespace webignition\BasilCompiler;

use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class VariableIdentifierGenerator
{
    public function generate(VariablePlaceholderCollection $variablePlaceholders): array
    {
        $identifiers = [];

        foreach ($variablePlaceholders as $placeholder) {
            $identifiers[$placeholder->getName()] = '$' . strtolower($placeholder->getName());
        }

        return $identifiers;
    }
}
