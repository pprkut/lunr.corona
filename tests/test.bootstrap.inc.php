<?php

$base = dirname(__FILE__) . "/..";

set_include_path(
    $base . "/system:" .
    $base . "/tests:" .
    $base . "/tests/system:" .
    get_include_path()
);

// Load and setup class file autloader
include_once("libraries/core/class.autoloader.inc.php");
spl_autoload_register(array(new Lunr\Libraries\Core\Autoloader(), 'load'));

?>
