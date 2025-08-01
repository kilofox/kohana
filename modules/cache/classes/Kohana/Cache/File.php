<?php

/**
 * [Kohana Cache](api/Kohana_Cache) File driver. Provides a file based
 * driver for the Kohana Cache library. This is one of the slowest
 * caching methods.
 *
 * ### Configuration example
 *
 * Below is an example of a _file_ server configuration.
 *
 *     return [
 *         // File driver group
 *         'file' => [
 *             // Using File driver
 *             'driver' => 'file',
 *             // Cache location
 *             'cache_dir' => APPPATH . 'cache/.kohana_cache',
 *         ],
 *     ];
 *
 * In cases where only one cache group is required, if the group is named `default` there is
 * no need to pass the group name when instantiating a cache instance.
 *
 * #### General cache group configuration settings
 *
 * Below are the settings available to all types of cache driver.
 *
 * Name           | Required | Description
 * -------------- | -------- | ---------------------------------------------------------------
 * driver         | __YES__  | (_string_) The driver type to use
 * cache_dir      | __NO__   | (_string_) The cache directory to use for this cache instance
 *
 * ### System requirements
 *
 * *  Kohana 3.0.x
 * *  PHP 5.2.4 or greater
 *
 * @package    Kohana/Cache
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Cache_File extends Cache implements Cache_GarbageCollect
{
    /**
     * Creates a hashed filename based on the string. This is used
     * to create shorter unique IDs for each cache filename.
     *
     *     // Create the cache filename
     *     $filename = Cache_File::filename($this->_sanitize_id($id));
     *
     * @param   string  $string  string to hash into filename
     * @return  string
     */
    protected static function filename($string)
    {
        return sha1($string) . '.cache';
    }

    /**
     * @var  string   the caching directory
     */
    protected $_cache_dir;

    /**
     * Constructs the file cache driver. This method cannot be invoked externally. The file cache driver must
     * be instantiated using the `Cache::instance()` method.
     *
     * @param   array  $config  config
     * @throws  Cache_Exception
     */
    protected function __construct(array $config)
    {
        // Setup parent
        parent::__construct($config);

        try {
            $directory = Arr::get($this->_config, 'cache_dir', Kohana::$cache_dir);
            $this->_cache_dir = new SplFileInfo($directory);
        } catch (UnexpectedValueException $e) {
            $this->_cache_dir = $this->_make_directory($directory, 0777, true);
        }

        // If the defined directory is a file, get out of here
        if ($this->_cache_dir->isFile()) {
            throw new Cache_Exception('Unable to create cache directory as a file already exists : :resource', [':resource' => $this->_cache_dir->getRealPath()]);
        }

        // Check the read status of the directory
        if (!$this->_cache_dir->isReadable()) {
            throw new Cache_Exception('Unable to read from the cache directory :resource', [':resource' => $this->_cache_dir->getRealPath()]);
        }

        // Check the write status of the directory
        if (!$this->_cache_dir->isWritable()) {
            throw new Cache_Exception('Unable to write to the cache directory :resource', [':resource' => $this->_cache_dir->getRealPath()]);
        }
    }

    /**
     * Retrieve a cached value entry by id.
     *
     *     // Retrieve cache entry from file group
     *     $data = Cache::instance('file')->get('foo');
     *
     *     // Retrieve cache entry from file group and return 'bar' if missing
     *     $data = Cache::instance('file')->get('foo', 'bar');
     *
     * @param string $id id of cache to entry
     * @param string $default default value to return if cache miss
     * @return  mixed
     * @throws Cache_Exception
     * @throws ErrorException
     */
    public function get($id, $default = null)
    {
        $filename = Cache_File::filename($this->_sanitize_id($id));
        $directory = $this->_resolve_directory($filename);

        // Wrap operations in try/catch to handle notices
        try {
            // Open file
            $file = new SplFileInfo($directory . $filename);

            // If file does not exist
            if (!$file->isFile()) {
                // Return default value
                return $default;
            } else {
                // Test the expiry
                if ($this->_is_expired($file)) {
                    // Delete the file
                    $this->_delete_file($file, false, true);
                    return $default;
                }

                // open the file to read data
                $data = $file->openFile();

                // Run first fgets(). Cache data starts from the second line
                // as the first contains the lifetime timestamp
                $data->fgets();

                $cache = '';

                while ($data->eof() === false) {
                    $cache .= $data->fgets();
                }

                return unserialize($cache);
            }
        } catch (ErrorException $e) {
            // Handle ErrorException caused by failed unserialization
            if ($e->getCode() === E_NOTICE) {
                throw new Cache_Exception(__METHOD__ . ' failed to unserialize cached object with message : ' . $e->getMessage());
            }

            // Otherwise throw the exception
            throw $e;
        } catch (RuntimeException $e) {
            // Catch RuntimeException when cache file deleted between isFile and openFile
            return $default;
        }
    }

    /**
     * Set a value to cache with id and lifetime
     *
     *     $data = 'bar';
     *
     *     // Set 'bar' to 'foo' in file group, using default expiry
     *     Cache::instance('file')->set('foo', $data);
     *
     *     // Set 'bar' to 'foo' in file group for 30 seconds
     *     Cache::instance('file')->set('foo', $data, 30);
     *
     * @param string $id id of cache entry
     * @param string $data data to set to cache
     * @param int $lifetime lifetime in seconds
     * @return bool
     * @throws Cache_Exception
     * @throws ErrorException
     */
    public function set($id, $data, $lifetime = null)
    {
        $filename = Cache_File::filename($this->_sanitize_id($id));
        $directory = $this->_resolve_directory($filename);

        // If lifetime is null
        if ($lifetime === null) {
            // Set to the default expiry
            $lifetime = Arr::get($this->_config, 'default_expire', Cache::DEFAULT_EXPIRE);
        }

        // Open directory
        $dir = new SplFileInfo($directory);

        // If the directory path is not a directory
        if (!$dir->isDir()) {
            $this->_make_directory($directory, 0777, true);
        }

        // Open file to inspect
        $resouce = new SplFileInfo($directory . $filename);
        $file = $resouce->openFile('w');

        try {
            $data = $lifetime . "\n" . serialize($data);
            $file->fwrite($data, strlen($data));
            return $file->fflush();
        } catch (ErrorException $e) {
            // If serialize through an error exception
            if ($e->getCode() === E_NOTICE) {
                // Throw a caching error
                throw new Cache_Exception(__METHOD__ . ' failed to serialize data for caching with message : ' . $e->getMessage());
            }

            // Else rethrow the error exception
            throw $e;
        }
    }

    /**
     * Delete a cache entry based on id
     *
     *     // Delete 'foo' entry from the file group
     *     Cache::instance('file')->delete('foo');
     *
     * @param string $id id to remove from cache
     * @return bool
     * @throws Cache_Exception
     */
    public function delete($id)
    {
        $filename = Cache_File::filename($this->_sanitize_id($id));
        $directory = $this->_resolve_directory($filename);

        return $this->_delete_file(new SplFileInfo($directory . $filename), false, true);
    }

    /**
     * Delete all cache entries.
     *
     * Beware of using this method when
     * using shared memory cache systems, as it will wipe every
     * entry within the system for all clients.
     *
     *     // Delete all cache entries in the file group
     *     Cache::instance('file')->delete_all();
     *
     * @return bool
     * @throws Cache_Exception
     */
    public function delete_all()
    {
        return $this->_delete_file($this->_cache_dir, true);
    }

    /**
     * Garbage collection method that cleans any expired
     * cache entries from the cache.
     *
     * @return  void
     * @throws Cache_Exception
     */
    public function garbage_collect()
    {
        $this->_delete_file($this->_cache_dir, true, false, true);
    }

    /**
     * Deletes files recursively and returns false on any errors
     *
     *     // Delete a file or folder whilst retaining parent directory and ignore all errors
     *     $this->_delete_file($folder, true, true);
     *
     * @param   SplFileInfo  $file                     file
     * @param   bool $retain_parent_directory retain the parent directory
     * @param   bool $ignore_errors ignore_errors to prevent all exceptions interrupting exec
     * @param   bool $only_expired only expired files
     * @return  bool
     * @throws  Cache_Exception
     */
    protected function _delete_file(SplFileInfo $file, $retain_parent_directory = false, $ignore_errors = false, $only_expired = false)
    {
        // Allow graceful error handling
        try {
            if ($file->isFile()) {
                try {
                    // Handle ignore files
                    if (in_array($file->getFilename(), $this->config('ignore_on_delete'))) {
                        $delete = false;
                    }
                    // If only expired is not set
                    elseif ($only_expired === false) {
                        // We want to delete the file
                        $delete = true;
                    }
                    // Otherwise...
                    else {
                        // Assess the file expiry to flag it for deletion
                        $delete = $this->_is_expired($file);
                    }

                    // If the delete flag is set delete file
                    if ($delete === true)
                        return unlink($file->getRealPath());
                    else
                        return false;
                } catch (ErrorException $e) {
                    // Catch any delete file warnings
                    if ($e->getCode() === E_WARNING) {
                        throw new Cache_Exception(__METHOD__ . ' failed to delete file : :file', [':file' => $file->getRealPath()]);
                    }
                }
            } elseif ($file->isDir()) {
                // Create new DirectoryIterator
                $files = new DirectoryIterator($file->getPathname());

                // Iterate over each entry
                while ($files->valid()) {
                    // Extract the entry name
                    $name = $files->getFilename();

                    // If the name is not a dot
                    if ($name !== '.' && $name !== '..') {
                        // Create new file resource
                        $fp = new SplFileInfo($files->getRealPath());
                        // Delete the file
                        $this->_delete_file($fp, $retain_parent_directory, $ignore_errors, $only_expired);
                    }

                    // Move the file pointer on
                    $files->next();
                }

                // If set to retain parent directory, return now
                if ($retain_parent_directory) {
                    return true;
                }

                try {
                    // Remove the files iterator
                    // (fixes Windows PHP which has permission issues with open iterators)
                    unset($files);

                    // Try to remove the parent directory
                    return rmdir($file->getRealPath());
                } catch (ErrorException $e) {
                    // Catch any delete directory warnings
                    if ($e->getCode() === E_WARNING) {
                        throw new Cache_Exception(__METHOD__ . ' failed to delete directory : :directory', [':directory' => $file->getRealPath()]);
                    }
                    throw $e;
                }
            } else {
                // We get here if a file has already been deleted
                return false;
            }
        }
        // Catch all exceptions
        catch (Exception $e) {
            // If ignore_errors is on
            if ($ignore_errors === true) {
                // Return
                return false;
            }
            // Throw exception
            throw $e;
        }

        return false;
    }

    /**
     * Resolves the cache directory real path from the filename
     *
     *      // Get the realpath of the cache folder
     *      $realpath = $this->_resolve_directory($filename);
     *
     * @param   string  $filename  filename to resolve
     * @return  string
     */
    protected function _resolve_directory($filename)
    {
        return $this->_cache_dir->getRealPath() . DIRECTORY_SEPARATOR . $filename[0] . $filename[1] . DIRECTORY_SEPARATOR;
    }

    /**
     * Makes the cache directory if it doesn't exist. Simply a wrapper for
     * `mkdir` to ensure DRY principles
     *
     * @link    https://www.php.net/manual/en/function.mkdir.php
     * @param   string    $directory    directory path
     * @param   int $mode chmod mode
     * @param   bool $recursive allows nested directories creation
     * @param   resource  $context      a stream context
     * @return  SplFileInfo
     * @throws  Cache_Exception
     */
    protected function _make_directory($directory, $mode = 0777, $recursive = false, $context = null)
    {
        // call mkdir according to the availability of a passed $context param
        $mkdir_result = $context ?
            mkdir($directory, $mode, $recursive, $context) :
            mkdir($directory, $mode, $recursive);

        // throw an exception if unsuccessful
        if (!$mkdir_result) {
            throw new Cache_Exception('Failed to create the defined cache directory : :directory', [':directory' => $directory]);
        }

        // chmod to solve potential umask issues
        chmod($directory, $mode);

        return new SplFileInfo($directory);
    }

    /**
     * Test if cache file is expired
     *
     * @param SplFileInfo $file the cache file
     * @return bool true if expired false otherwise
     * @throws Cache_Exception
     */
    protected function _is_expired(SplFileInfo $file)
    {
        // Open the file and parse data
        $created = $file->getMTime();
        $data = $file->openFile();
        $lifetime = (int) $data->fgets();

        // If we're at the EOF at this point, corrupted!
        if ($data->eof()) {
            throw new Cache_Exception(__METHOD__ . ' corrupted cache file!');
        }

        //close file
        $data = null;

        // test for expiry and return
        return $lifetime !== 0 && $created + $lifetime < time();
    }

}
