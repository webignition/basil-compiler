<?php

namespace webignition\BasilCompiler;

use webignition\BasilCompilableSourceFactory\VariableNames;

class ExternalVariableIdentifiers
{
    private string $domNavigatorCrawlerName;
    private string $environmentVariableArrayName;
    private string $pantherClientName;
    private string $pantherCrawlerName;
    private string $phpUnitTestCaseName;
    private string $webDriverElementInspectorName;
    private string $webDriverElementMutatorName;
    private string $actionFactoryName;
    private string $assertionFactoryName;

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
