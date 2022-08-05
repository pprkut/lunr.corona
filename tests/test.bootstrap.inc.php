<?php

/**
 * PHPUnit bootstrap file.
 *
 * Set include path and initialize autoloader.
 *
 * @package   Tests
 * @author    Heinz Wiesinger <heinz@m2mobi.com>
 * @copyright 2011-2018, M2Mobi BV, Amsterdam, The Netherlands
 * @license   http://lunr.nl/LICENSE MIT License
 */

$base = __DIR__ . '/..';

set_include_path(
    $base . '/src:' .
    $base . '/tests:' .
    $base . '/tests/statics:' .
    get_include_path()
);

if (file_exists($base . '/vendor/autoload.php') == TRUE)
{
    // Load composer autoloader.
    require_once $base . '/vendor/autoload.php';
}
else
{
    // Load decomposer autoloader.
    require_once $base . '/decomposer.autoload.inc.php';
}

if (defined('TEST_STATICS') === FALSE)
{
    define('TEST_STATICS', __DIR__ . '/statics');
}

?>
