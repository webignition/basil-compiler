<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

class VariableNameResolver
{
    const PLACEHOLDER_TEMPLATE = '{{ %s }}';

    public function resolve(string $content, array $variableIdentifiers): string
    {
        $search = [];
        $replace = [];

        foreach ($variableIdentifiers as $identifier => $name) {
            $search[] = sprintf(self::PLACEHOLDER_TEMPLATE, $identifier);
            $replace[] = $name;
        }

        return (string) str_replace($search, $replace, $content);
    }
}
