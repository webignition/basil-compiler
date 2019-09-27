<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;

trait SubmitActionDataProviderTrait
{
    public function submitActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'interaction action (submit), element identifier' => [
                'action' => $actionFactory->createFromActionString(
                    'submit ".selector"'
                ),
            ],
        ];
    }
}
