<?php

/**
 * TestCase for testing a database
 *
 * @package    Kohana/UnitTest
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    https://kohana.top/license
 */
abstract class Kohana_Unittest_Database_TestCase extends PHPUnit_Extensions_Database_TestCase
{
    /**
     * Make sure PHPUnit backs up globals
     * @var bool
     */
    protected $backupGlobals = false;

    /**
     * A set of unittest helpers that are shared between normal / database
     * testcases
     * @var Kohana_Unittest_Helpers
     */
    protected $_helpers = null;

    /**
     * A default set of environment to be applied before each test
     * @var array
     */
    protected $environmentDefault = [];

    /**
     * The kohana database connection that PHPUnit should use for this test
     * @var string
     */
    protected $_database_connection = 'default';

    /**
     * Creates a predefined environment using the default environment
     *
     * Extending classes that have their own setUp() should call
     * parent::setUp()
     *
     * @return void
     * @throws Kohana_Exception
     * @throws ReflectionException
     */
    public function setUp()
    {
        $this->_helpers = new Kohana_Unittest_Helpers;

        $this->setEnvironment($this->environmentDefault);

        parent::setUp();
    }

    /**
     * Restores the original environment overridden with setEnvironment()
     *
     * Extending classes that have their own tearDown()
     * should call parent::tearDown()
     *
     * @return void
     * @throws Kohana_Exception
     * @throws ReflectionException
     */
    public function tearDown()
    {
        $this->_helpers->restore_environment();

        parent::tearDown();
    }

    /**
     * Creates a connection to the unittesting database
     *
     * @return PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     * @throws Kohana_Exception
     */
    public function getConnection()
    {
        // Get the unittesting db connection
        $config = Kohana::$config->load('database.' . $this->_database_connection);

        if (strtolower($config['type']) !== 'pdo') {
            $config['connection']['dsn'] = strtolower($config['type']) . ':' .
                'host=' . $config['connection']['hostname'] . ';' .
                'dbname=' . $config['connection']['database'];
        }

        $pdo = new PDO(
            $config['connection']['dsn'], $config['connection']['username'], $config['connection']['password']
        );

        return $this->createDefaultDBConnection($pdo, $config['connection']['database']);
    }

    /**
     * Gets a connection to the unittest database
     *
     * @return Kohana_Database The database connection
     * @throws Kohana_Exception
     */
    public function getKohanaConnection()
    {
        return Database::instance(Kohana::$config->load('unittest')->db_connection);
    }

    /**
     * Removes all kohana related cache files in the cache directory
     *
     * @return void
     */
    public function cleanCacheDir()
    {
        Kohana_Unittest_Helpers::clean_cache_dir();
    }

    /**
     * Helper function that replaces all occurrences of '/' with
     * the OS-specific directory separator
     *
     * @param string $path The path to act on
     * @return string
     */
    public function dirSeparator($path)
    {
        return Kohana_Unittest_Helpers::dir_separator($path);
    }

    /**
     * Allows easy setting & backing up of environment config
     *
     * Option types are checked in the following order:
     *
     * * Server Var
     * * Static Variable
     * * Config option
     *
     * @param array $environment List of environment to set
     * @return false|null
     * @throws Kohana_Exception
     * @throws ReflectionException
     */
    public function setEnvironment(array $environment)
    {
        return $this->_helpers->set_environment($environment);
    }

    /**
     * Check for internet connectivity
     *
     * @return bool Whether an internet connection is available
     */
    public function hasInternet()
    {
        return Kohana_Unittest_Helpers::has_internet();
    }

}
