<?php

declare(strict_types=1);

namespace webignition\BasilCompiler;

class VariablePlaceholderResolver
{
    /**
     * @param string $content
     * @param array<string, string> $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function resolve(string $content, array $variableIdentifiers): string
    {
        $search = [];
        $replace = [];

        foreach ($variableIdentifiers as $identifier => $name) {
            $search[] = sprintf('{{ %s }}', $identifier);
            $replace[] = $name;
        }

        $resolvedContent = (string) str_replace($search, $replace, $content);

        $placeholderMatches = [];
        if (preg_match('/{{ [^${]+ }}/', $resolvedContent, $placeholderMatches)) {
            $unresolvedPlaceholder = trim($placeholderMatches[0], '{} ');

            throw new UnresolvedPlaceholderException($unresolvedPlaceholder, $content);
        }

        return $resolvedContent;
    }
}
