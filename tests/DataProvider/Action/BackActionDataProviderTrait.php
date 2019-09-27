<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;

trait BackActionDataProviderTrait
{
    public function backActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'no-arguments action (back)' => [
                'action' => $actionFactory->createFromActionString(
                    'back'
                ),
            ],
        ];
    }
}
