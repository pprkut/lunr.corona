<?php

/**
 * PHPUnit bootstrap file.
 *
 * Set include path and initialize autoloader.
 *
 * PHP Version 5.3
 *
 * @category   Loaders
 * @package    Tests
 * @subpackage Tests
 * @author     M2Mobi <info@m2mobi.com>
 * @author     Heinz Wiesinger <heinz@m2mobi.com>
 */

$base = dirname(__FILE__) . '/..';

set_include_path(
    $base . '/system:' .
    $base . '/tests:' .
    $base . '/tests/mocks:' .
    $base . '/tests/system:' .
    get_include_path()
);

// Load and setup class file autloader
require_once 'libraries/core/class.autoloader.inc.php';
spl_autoload_register(array(new Lunr\Libraries\Core\Autoloader(), 'load'));

?>
