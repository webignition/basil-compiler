<?php

namespace webignition\BasilTranspiler\Tests\Functional;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Tests\Services\ExecutableCallFactory;
use webignition\BasilTranspiler\VariableNames;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

abstract class AbstractTestCase extends PantherTestCase
{
    const FIXTURES_RELATIVE_PATH = '/fixtures';
    const FIXTURES_HTML_RELATIVE_PATH = '/html';

    const DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME = '$domCrawlerNavigator';
    const PHPUNIT_TEST_CASE_VARIABLE_NAME = '$this';
    const PANTHER_CLIENT_VARIABLE_NAME = 'self::$client';

    const VARIABLE_IDENTIFIERS = [
        VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
        VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
        VariableNames::PANTHER_CLIENT => self::PANTHER_CLIENT_VARIABLE_NAME,
    ];

    /**
     * @var Client
     */
    protected static $client;

    /**
     * @var ExecutableCallFactory
     */
    protected $executableCallFactory;

    protected function setUp(): void
    {
        self::$webServerDir = (string) realpath(
            __DIR__  . '/..' . self::FIXTURES_RELATIVE_PATH . self::FIXTURES_HTML_RELATIVE_PATH
        );

        self::$client = self::createPantherClient();
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }

    protected function createExecutableCall(
        TranspilationResultInterface $transpilationResult,
        array $variableIdentifiers,
        string $fixture,
        array $additionalPreLines = [],
        array $additionalPostLines = [],
        array $additionalUseStatements = []
    ): string {
        return $this->executableCallFactory->create(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            array_merge(
                [
                    '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                    '$domCrawlerNavigator = Navigator::create($crawler); ',
                ],
                $additionalPreLines
            ),
            $additionalPostLines,
            new UseStatementCollection(array_merge(
                [
                    new UseStatement(Navigator::class),
                ],
                $additionalUseStatements
            ))
        );
    }
}
