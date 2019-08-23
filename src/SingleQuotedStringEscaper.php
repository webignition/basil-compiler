<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

class SingleQuotedStringEscaper
{
    const DEFAULT_SLASH_QUOTE_PLACEHOLDER_VALUE = 'slash-quote-placeholder';

    private $placeholderFactory;

    public function __construct(PlaceholderFactory $placeholderFactory)
    {
        $this->placeholderFactory = $placeholderFactory;
    }

    public static function create(): SingleQuotedStringEscaper
    {
        return new SingleQuotedStringEscaper(
            PlaceholderFactory::createFactory()
        );
    }

    public function escape(string $string): string
    {
        $slashQuotePlaceholder = $this->placeholderFactory->create(
            $string,
            self::DEFAULT_SLASH_QUOTE_PLACEHOLDER_VALUE
        );

        $string = str_replace("\'", $slashQuotePlaceholder, $string);
        $string = str_replace("'", "\'", $string);
        $string = str_replace($slashQuotePlaceholder, "\\\\\'", $string);

        return $string;
    }
}
