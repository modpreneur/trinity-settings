<?php

/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Tests;

use  Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Class TestCase
 * @package Trinity\Bundle\SettingsBundle\Tests
 */
class TestCase extends WebTestCase
{
    
    protected static function getKernelClass()
    {
        require_once __DIR__.'/Functional/app/AppKernel.php';

        return 'Trinity\Bundle\SettingsBundle\Tests\Functional\app\AppKernel';
    }

}