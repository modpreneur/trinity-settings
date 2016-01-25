<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Tests\Functional;

use Trinity\Bundle\SettingsBundle\Manager\SettingsManager;


/**
 * Class ProductTest
 * @package Trinity\Bundle\SearchBundle\Tests\Functional
 */
class SettingTest extends WebTestCase
{
    public function testDefaultValue()
    {
        /** @var SettingsManager $settings */
        $settings = $this->get( 'trinity_settings' );

        $settings->setDefault( 'default_value', 'hello' );
        $this->assertEquals( 'hello', $settings->get('default_value') );

        $settings->set( 'default_value', 'new value' );
        $this->assertEquals( 'new value', $settings->get('default_value') );
    }

}