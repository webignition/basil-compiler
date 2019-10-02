<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;

trait SetActionDataProviderTrait
{
    public function setActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'input action, element identifier, literal value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to "value"'
                ),
            ],
            'input action, element identifier, element value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to ".source"'
                ),
            ],
            'input action, element identifier, attribute value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to ".source".attribute_name'
                ),
            ],
            'input action, browser property' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to $browser.size'
                ),
            ],
            'input action, page property' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to $page.url'
                ),
            ],
            'input action, environment value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to $env.KEY'
                ),
            ],
        ];
    }
}