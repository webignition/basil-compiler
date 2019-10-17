<?php

namespace webignition\BasilTranspiler\Tests\Functional;

use Facebook\WebDriver\WebDriverDimension;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
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

    public static function setUpBeforeClass(): void
    {
        self::$webServerDir = (string) realpath(
            __DIR__  . '/..' . self::FIXTURES_RELATIVE_PATH . self::FIXTURES_HTML_RELATIVE_PATH
        );

        self::$client = self::createPantherClient();
        self::$client->getWebDriver()->manage()->window()->setSize(new WebDriverDimension(1200, 1100));
    }

    protected function setUp(): void
    {
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }

    protected function createExecutableCall(
        SourceInterface $source,
        string $fixture,
        array $variableIdentifiers = [],
        array $additionalSetupStatements = [],
        array $additionalTeardownStatements = [],
        ?MetadataInterface $additionalMetadata = null
    ): string {
        $metadata = (new Metadata())
            ->withClassDependencies(new ClassDependencyCollection([
                new ClassDependency(Navigator::class),
            ]));

        if ($additionalMetadata instanceof MetadataInterface) {
            $metadata = $metadata->merge([$additionalMetadata]);
        }

        return $this->executableCallFactory->create(
            $source,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            array_merge(
                [
                    '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                    '$domCrawlerNavigator = Navigator::create($crawler); ',
                ],
                $additionalSetupStatements
            ),
            $additionalTeardownStatements,
            $metadata
        );
    }

    protected function createExecutableCallWithReturn(
        SourceInterface $source,
        string $fixture,
        array $variableIdentifiers = [],
        array $additionalSetupStatements = [],
        array $additionalTeardownStatements = [],
        ?MetadataInterface $additionalMetadata = null
    ): string {
        $metadata = (new Metadata())
            ->withClassDependencies(new ClassDependencyCollection([
                new ClassDependency(Navigator::class),
            ]));

        if ($additionalMetadata instanceof MetadataInterface) {
            $metadata = $metadata->merge([$additionalMetadata]);
        }

        return $this->executableCallFactory->createWithReturn(
            $source,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            array_merge(
                [
                    '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                    '$domCrawlerNavigator = Navigator::create($crawler); ',
                ],
                $additionalSetupStatements
            ),
            $additionalTeardownStatements,
            $metadata
        );
    }
}
