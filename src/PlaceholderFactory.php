<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

class PlaceholderFactory
{
    const TEMPLATE = '{{ %s }}';

    public static function createFactory(): PlaceholderFactory
    {
        return new PlaceholderFactory();
    }

    public function create(string $content, string $placeholderContent): string
    {
        $placeholder = sprintf(self::TEMPLATE, $placeholderContent);
        $mutationCount = 0;

        while (substr_count($content, $placeholder) > 0) {
            $mutationCount++;
            $placeholder = sprintf(self::TEMPLATE, $placeholderContent . (string) $mutationCount);
        }

        return $placeholder;
    }
}
