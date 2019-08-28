<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNames;
use webignition\BasilTranspiler\Model\VariablePlaceholder;

class PageObjectValueTranspiler extends AbstractObjectValueTranspiler implements TranspilerInterface
{
    const PROPERTY_NAME_TITLE = 'title';
    const PROPERTY_NAME_URL = 'url';

    private $pantherClientVariablePlaceholder;

    public function __construct()
    {
        $this->pantherClientVariablePlaceholder = new VariablePlaceholder(VariableNames::PANTHER_CLIENT);
    }

    public static function createTranspiler(): PageObjectValueTranspiler
    {
        return new PageObjectValueTranspiler();
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof ObjectValueInterface) {
            return false;
        }

        if (ValueTypes::PAGE_OBJECT_PROPERTY !== $model->getType()) {
            return false;
        }

        return ObjectNames::PAGE === $model->getObjectName();
    }

    protected function getTranspiledValueMap(): array
    {
        return [
            self::PROPERTY_NAME_TITLE => (string) $this->pantherClientVariablePlaceholder . '->getTitle()',
            self::PROPERTY_NAME_URL => (string) $this->pantherClientVariablePlaceholder . '->getCurrentURL()',
        ];
    }

    protected function getVariablePlaceholders(): VariablePlaceholderCollection
    {
        return new VariablePlaceholderCollection([
            $this->pantherClientVariablePlaceholder,
        ]);
    }
}
