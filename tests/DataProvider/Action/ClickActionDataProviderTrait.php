<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;

trait ClickActionDataProviderTrait
{
    public function clickActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'interaction action (click), element identifier' => [
                'action' => $actionFactory->createFromActionString(
                    'click ".selector"'
                ),
            ],
        ];
    }
}
