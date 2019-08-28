<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilTranspiler\Model\VariablePlaceholder;

class VariablePlaceholderResolver
{
    public function resolve(string $content, array $variableIdentifiers): string
    {
        $search = [];
        $replace = [];

        foreach ($variableIdentifiers as $identifier => $name) {
            $search[] = sprintf(VariablePlaceholder::TEMPLATE, $identifier);
            $replace[] = $name;
        }

        return (string) str_replace($search, $replace, $content);
    }
}
