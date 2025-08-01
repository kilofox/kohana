<?php

/**
 * A version of the stock PHPUnit testsuite that supports whitelisting and
 * blacklisting for code coverage filter
 */
abstract class Kohana_Unittest_TestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Holds the details of files that should be white and blacklisted for
     * code coverage
     *
     * @var array
     */
    protected $_filter_calls = [
        'addFileToBlacklist' => [],
        'addDirectoryToBlacklist' => [],
        'addFileToWhitelist' => []
    ];

    /**
     * Runs the tests and collects their result in a TestResult.
     *
     * @param PHPUnit_Framework_TestResult|null $result
     * @param mixed $filter
     * @param array $groups
     * @param array $excludeGroups
     * @param bool $processIsolation
     * @return PHPUnit_Framework_TestResult
     */
    public function run(PHPUnit_Framework_TestResult $result = null, $filter = false, array $groups = [], array $excludeGroups = [], $processIsolation = false)
    {

        // Get the code coverage filter from the suite's result object
        $coverage = $result->getCodeCoverage();

        if ($coverage) {
            $coverage_filter = $coverage->filter();

            // Apply the white and blacklisting
            foreach ($this->_filter_calls as $method => $args) {
                foreach ($args as $arg) {
                    $coverage_filter->$method($arg);
                }
            }
        }

        return parent::run($result);
    }

    /**
     * Queues a file to be added to the code coverage blacklist when the suite runs
     * @param string $file
     */
    public function addFileToBlacklist($file)
    {
        $this->_filter_calls['addFileToBlacklist'][] = $file;
    }

    /**
     * Queues a directory to be added to the code coverage blacklist when the suite runs
     * @param string $dir
     */
    public function addDirectoryToBlacklist($dir)
    {
        $this->_filter_calls['addDirectoryToBlacklist'][] = $dir;
    }

    /**
     * Queues a file to be added to the code coverage whitelist when the suite runs
     * @param string $file
     */
    public function addFileToWhitelist($file)
    {
        $this->_filter_calls['addFileToWhitelist'][] = $file;
    }

}
