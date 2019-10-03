<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilCompilationSource\VariablePlaceholder;

class PlaceholderFactory
{
    public static function createFactory(): PlaceholderFactory
    {
        return new PlaceholderFactory();
    }

    public function create(string $content, string $placeholderContent): string
    {
        $placeholder = sprintf(VariablePlaceholder::TEMPLATE, $placeholderContent);
        $mutationCount = 0;

        while (substr_count($content, $placeholder) > 0) {
            $mutationCount++;
            $placeholder = sprintf(VariablePlaceholder::TEMPLATE, $placeholderContent . (string) $mutationCount);
        }

        return $placeholder;
    }
}
