<?php

/**
 * This file contains the EinvironmentTest class.
 *
 * PHP Version 5.3
 *
 * @category   Libraries
 * @package    Core
 * @subpackage Tests
 * @author     M2Mobi <info@m2mobi.com>
 * @author     Heinz Wiesinger <heinz@m2mobi.com>
 */

namespace Lunr;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * This class tests for a proper test environment.
 *
 * @category   Libraries
 * @package    Core
 * @subpackage Tests
 * @author     Heinz Wiesinger <heinz@m2mobi.com>
 * @covers     Lunr\EnvironmentTest
 */
class EnvironmentTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test whether we have the runkit_function_redefine method available.
     */
    public function testRunkit()
    {
        $this->assertTrue(function_exists('runkit_function_redefine'));
    }

}

?>
