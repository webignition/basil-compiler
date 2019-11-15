<?php

namespace webignition\BasilCompiler\Tests;

use webignition\BasilCompilableSourceFactory\VariableNames;

class ExternalVariableIdentifiers
{
    private const DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME = '$this->navigator';
    private const ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME = '$_ENV';
    private const PANTHER_CLIENT_VARIABLE_NAME = 'self::$client';
    private const PANTHER_CRAWLER_VARIABLE_NAME = 'self::$crawler';
    private const PHPUNIT_TEST_CASE_VARIABLE_NAME = '$this';
    private const WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME = 'self::$inspector';
    private const WEBDRIVER_ELEMENT_MUTATOR_VARIABLE_NAME = 'self::$mutator';

    public const IDENTIFIERS = [
        VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
        VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
        VariableNames::PANTHER_CLIENT => self::PANTHER_CLIENT_VARIABLE_NAME,
        VariableNames::PANTHER_CRAWLER => self::PANTHER_CRAWLER_VARIABLE_NAME,
        VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
        VariableNames::WEBDRIVER_ELEMENT_MUTATOR => self::WEBDRIVER_ELEMENT_MUTATOR_VARIABLE_NAME,
    ];
}
