<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;

trait ReloadActionDataProviderTrait
{
    public function reloadActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'no-arguments action (reload)' => [
                'action' => $actionFactory->createFromActionString(
                    'reload'
                ),
            ],
        ];
    }
}
