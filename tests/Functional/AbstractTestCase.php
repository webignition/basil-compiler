<?php

namespace webignition\BasilTranspiler\Tests\Functional;

use Facebook\WebDriver\WebDriverDimension;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependency;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
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
        self::$client->getWebDriver()->manage()->window()->setSize(new WebDriverDimension(1200, 1100));

        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }

    protected function createExecutableCall(
        CompilableSourceInterface $compilableSource,
        array $variableIdentifiers,
        string $fixture,
        array $additionalSetupStatements = [],
        array $additionalTeardownStatements = [],
        array $additionalClassDependencies = []
    ): string {
        return $this->executableCallFactory->create(
            $compilableSource,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            array_merge(
                [
                    '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                    '$domCrawlerNavigator = Navigator::create($crawler); ',
                ],
                $additionalSetupStatements
            ),
            $additionalTeardownStatements,
            new ClassDependencyCollection(array_merge(
                [
                    new ClassDependency(Navigator::class),
                ],
                $additionalClassDependencies
            ))
        );
    }
}
