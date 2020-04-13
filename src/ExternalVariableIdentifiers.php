<?php

namespace webignition\BasilCompiler;

use webignition\BasilCompilableSourceFactory\VariableNames;

class ExternalVariableIdentifiers
{
    private $domNavigatorCrawlerName;
    private $environmentVariableArrayName;
    private $pantherClientName;
    private $pantherCrawlerName;
    private $phpUnitTestCaseName;
    private $webDriverElementInspectorName;
    private $webDriverElementMutatorName;
    private $actionFactoryName;
    private $assertionFactoryName;

    public function __construct(
        string $domNavigatorCrawlerName,
        string $environmentVariableArrayName,
        string $pantherClientName,
        string $pantherCrawlerName,
        string $phpUnitTestCaseName,
        string $webDriverElementInspectorName,
        string $webDriverElementMutatorName,
        string $actionFactoryName,
        string $assertionFactoryName
    ) {
        $this->domNavigatorCrawlerName = $domNavigatorCrawlerName;
        $this->environmentVariableArrayName = $environmentVariableArrayName;
        $this->pantherClientName = $pantherClientName;
        $this->pantherCrawlerName = $pantherCrawlerName;
        $this->phpUnitTestCaseName = $phpUnitTestCaseName;
        $this->webDriverElementInspectorName = $webDriverElementInspectorName;
        $this->webDriverElementMutatorName = $webDriverElementMutatorName;
        $this->actionFactoryName = $actionFactoryName;
        $this->assertionFactoryName = $assertionFactoryName;
    }

    /**
     * @return array<string, string>
     */
    public function get(): array
    {
        return [
            VariableNames::ACTION_FACTORY => $this->actionFactoryName,
            VariableNames::ASSERTION_FACTORY => $this->assertionFactoryName,
            VariableNames::DOM_CRAWLER_NAVIGATOR => $this->domNavigatorCrawlerName,
            VariableNames::ENVIRONMENT_VARIABLE_ARRAY => $this->environmentVariableArrayName,
            VariableNames::PANTHER_CLIENT => $this->pantherClientName,
            VariableNames::PANTHER_CRAWLER => $this->pantherCrawlerName,
            VariableNames::PHPUNIT_TEST_CASE => $this->phpUnitTestCaseName,
            VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => $this->webDriverElementInspectorName,
            VariableNames::WEBDRIVER_ELEMENT_MUTATOR => $this->webDriverElementMutatorName,
        ];
    }
}
