<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;

trait UnhandledActionsDataProviderTrait
{
    public function unhandledActionsDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'unhandled action: non-value object' => [
                'value' => new \stdClass(),
            ],
            'input action, element identifier, literal value' => [
                'value' => $actionFactory->createFromActionString(
                    'set ".selector" to "value"'
                ),
            ],
            'input action, element identifier, element value' => [
                'value' => $actionFactory->createFromActionString(
                    'set ".selector" to ".source"'
                ),
            ],
            'input action, element identifier, attribute value' => [
                'value' => $actionFactory->createFromActionString(
                    'set ".selector" to ".source".attribute_name'
                ),
            ],
            'input action, browser property' => [
                'value' => $actionFactory->createFromActionString(
                    'set ".selector" to $browser.size'
                ),
            ],
            'input action, page property' => [
                'value' => $actionFactory->createFromActionString(
                    'set ".selector" to $page.url'
                ),
            ],
            'input action, environment value' => [
                'value' => $actionFactory->createFromActionString(
                    'set ".selector" to $env.KEY'
                ),
            ],
        ];
    }
}
