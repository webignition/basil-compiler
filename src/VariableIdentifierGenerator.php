<?php

namespace webignition\BasilCompiler;

use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class VariableIdentifierGenerator
{
    /**
     * @param VariablePlaceholderCollection $variablePlaceholders
     *
     * @return array<string, string>
     */
    public function generate(VariablePlaceholderCollection $variablePlaceholders): array
    {
        $identifiers = [];

        foreach ($variablePlaceholders as $placeholder) {
            $identifiers[$placeholder->getName()] = '$' . strtolower($placeholder->getName());
        }

        return $identifiers;
    }
}
