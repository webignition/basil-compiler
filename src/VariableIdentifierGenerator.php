<?php

namespace webignition\BasilCompiler;

use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;

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
            /** @var VariablePlaceholder $placeholder*/
            $identifiers[$placeholder->getName()] = '$' . strtolower($placeholder->getName());
        }

        return $identifiers;
    }
}
