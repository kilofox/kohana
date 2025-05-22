<?php
// Sanity check, installation should only be checked from index.php
defined('SYSPATH') or exit('Installation tests must be loaded from within index.php!');

// Clear the realpath() cache
clearstatcache(true);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Kohana Installation</title>
        <style>
            body { width: 42em; margin: 0 auto; font-family: sans-serif; background: #fff; font-size: 1em; }
            h1 { letter-spacing: -0.04em; }
            h1 + p { margin: 0 0 2em; color: #333; font-size: 90%; font-style: italic; }
            code { font-family: monaco, monospace; }
            table { border-collapse: collapse; width: 100%; }
            table th,
            table td { padding: 0.4em; text-align: left; vertical-align: top; }
            table th { width: 12em; font-weight: normal; }
            table tr:nth-child(odd) { background: #eee; }
            table td.pass { color: #191; }
            table td.fail { color: #911; }
            #results { padding: 0.8em; color: #fff; font-size: 1.5em; }
            #results.pass { background: #191; }
            #results.fail { background: #911; }
        </style>
    </head>
    <body>
        <h1>Environment Tests</h1>
        <p>
            The following tests have been run to determine if <a href="http://kohana.top">Kohana</a> will work in your environment.
            If any of the tests have failed, consult the <a href="https://kohana.top/guide/about.install">documentation</a>
            for more information on how to correct the problem.
        </p>
        <?php $failed = false ?>
        <table>
            <tr>
                <th>PHP Version</th>
                <?php if (PHP_VERSION_ID >= 50600): ?>
                    <td class="pass"><?= PHP_VERSION ?></td>
                <?php else: $failed = true ?>
                    <td class="fail">Kohana requires PHP 5.6.0 or newer, this version is <?= PHP_VERSION ?>.</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>System Directory</th>
                <?php if (is_file(SYSPATH . 'classes/Kohana' . EXT)): ?>
                    <td class="pass"><?= SYSPATH ?></td>
                <?php else: $failed = true ?>
                    <td class="fail">The configured <code>system</code> directory does not exist or does not contain required files.</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>Application Directory</th>
                <?php if (is_file(APPPATH . 'bootstrap' . EXT)): ?>
                    <td class="pass"><?= APPPATH ?></td>
                <?php else: $failed = true ?>
                    <td class="fail">The configured <code>application</code> directory does not exist or does not contain required files.</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>Cache Directory</th>
                <?php if (is_dir(APPPATH . 'cache') && is_writable(APPPATH . 'cache')): ?>
                    <td class="pass"><?= APPPATH . 'cache' . DIRECTORY_SEPARATOR ?></td>
                <?php else: $failed = true ?>
                    <td class="fail">The <code><?= APPPATH . 'cache' . DIRECTORY_SEPARATOR ?></code> directory is not writable.</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>Logs Directory</th>
                <?php if (is_dir(APPPATH . 'logs') && is_writable(APPPATH . 'logs')): ?>
                    <td class="pass"><?= APPPATH . 'logs' . DIRECTORY_SEPARATOR ?></td>
                <?php else: $failed = true ?>
                    <td class="fail">The <code><?= APPPATH . 'logs' . DIRECTORY_SEPARATOR ?></code> directory is not writable.</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>PCRE UTF-8</th>
                <?php if (!@preg_match('/^.$/u', 'ñ')): $failed = true ?>
                    <td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support.</td>
                <?php elseif (!@preg_match('/^\pL$/u', 'ñ')): $failed = true ?>
                    <td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with Unicode property support.</td>
                <?php else: ?>
                    <td class="pass">Pass</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>SPL Enabled</th>
                <?php if (function_exists('spl_autoload_register')): ?>
                    <td class="pass">Pass</td>
                <?php else: $failed = true ?>
                    <td class="fail">PHP <a href="http://www.php.net/spl">SPL</a> is either not loaded or not compiled in.</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>Reflection Enabled</th>
                <?php if (class_exists('ReflectionClass')): ?>
                    <td class="pass">Pass</td>
                <?php else: $failed = true ?>
                    <td class="fail">PHP <a href="http://www.php.net/reflection">reflection</a> is either not loaded or not compiled in.</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>Filters Enabled</th>
                <?php if (function_exists('filter_list')): ?>
                    <td class="pass">Pass</td>
                <?php else: $failed = true ?>
                    <td class="fail">The <a href="http://www.php.net/filter">filter</a> extension is either not loaded or not compiled in.</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>Iconv Extension Loaded</th>
                <?php if (extension_loaded('iconv')): ?>
                    <td class="pass">Pass</td>
                <?php else: $failed = true ?>
                    <td class="fail">The <a href="http://php.net/iconv">iconv</a> extension is not loaded.</td>
                <?php endif ?>
            </tr>
            <?php if (extension_loaded('mbstring')): ?>
                <tr>
                    <th>Mbstring Not Overloaded</th>
                    <?php if (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING): $failed = true ?>
                        <td class="fail">The <a href="http://php.net/mbstring">mbstring</a> extension is overloading PHP's native string functions.</td>
                    <?php else: ?>
                        <td class="pass">Pass</td>
                    <?php endif ?>
                </tr>
            <?php endif ?>
            <tr>
                <th>Character Type (CTYPE) Extension</th>
                <?php if (!function_exists('ctype_digit')): $failed = true ?>
                    <td class="fail">The <a href="http://php.net/ctype">ctype</a> extension is not enabled.</td>
                <?php else: ?>
                    <td class="pass">Pass</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>URI Determination</th>
                <?php if (isset($_SERVER['REQUEST_URI']) or isset($_SERVER['PHP_SELF']) or isset($_SERVER['PATH_INFO'])): ?>
                    <td class="pass">Pass</td>
                <?php else: $failed = true ?>
                    <td class="fail">Neither <code>$_SERVER['REQUEST_URI']</code>, <code>$_SERVER['PHP_SELF']</code>, or <code>$_SERVER['PATH_INFO']</code> is available.</td>
                <?php endif ?>
            </tr>
        </table>
        <?php if ($failed === true): ?>
            <p id="results" class="fail">✘ Kohana may not work correctly with your environment.</p>
        <?php else: ?>
            <p id="results" class="pass">✔ Your environment passed all requirements.<br />
                Remove or rename the <code>install<?= EXT ?></code> file now.</p>
        <?php endif ?>
        <h1>Optional Tests</h1>
        <p>
            The following extensions are not required to run the Kohana core, but if enabled can provide access to additional classes.
        </p>
        <table>
            <tr>
                <th>PECL HTTP Enabled</th>
                <?php if (extension_loaded('http')): ?>
                    <td class="pass">Pass</td>
                <?php else: ?>
                    <td class="fail">Kohana can use the <a href="http://php.net/http">http</a> extension for the Request_Client_External class.</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>cURL Enabled</th>
                <?php if (extension_loaded('curl')): ?>
                    <td class="pass">Pass</td>
                <?php else: ?>
                    <td class="fail">Kohana can use the <a href="http://php.net/curl">cURL</a> extension for the Request_Client_External class.</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>OpenSSL Enabled</th>
                <?php if (extension_loaded('openssl')): ?>
                    <td class="pass">Pass</td>
                <?php else: ?>
                    <td class="fail">Kohana can use the <a href="http://php.net/openssl">OpenSSL</a> extension for the Encrypt class.</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>Mcrypt Enabled</th>
                <?php if (extension_loaded('mcrypt')): ?>
                    <td class="pass">Pass</td>
                <?php else: ?>
                    <td class="fail">Kohana can use the <a href="http://php.net/mcrypt">Mcrypt</a> extension for the Encrypt class.</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>GD Enabled</th>
                <?php if (function_exists('gd_info')): ?>
                    <td class="pass">Pass</td>
                <?php else: ?>
                    <td class="fail">Kohana requires <a href="http://php.net/gd">GD</a> v2 for the Image class.</td>
                <?php endif ?>
            </tr>
            <tr>
                <th>PDO Enabled</th>
                <?php if (class_exists('PDO')): ?>
                    <td class="pass">Pass</td>
                <?php else: ?>
                    <td class="fail">Kohana can use <a href="http://php.net/pdo">PDO</a> to support databases.</td>
                <?php endif ?>
            </tr>
        </table>
    </body>
</html>
