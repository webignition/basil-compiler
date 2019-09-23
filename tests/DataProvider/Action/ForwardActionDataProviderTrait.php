<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;

trait ForwardActionDataProviderTrait
{
    public function forwardActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'no-arguments action (forward)' => [
                'value' => $actionFactory->createFromActionString(
                    'forward'
                ),
            ],
        ];
    }
}
