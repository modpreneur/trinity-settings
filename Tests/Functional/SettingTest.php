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

    public function testSetValue(){
        /** @var SettingsManager $settings */
        $settings = $this->get( 'trinity.settings' );

        $settings->set('string_abc', 'abc');
        $this->assertEquals('abc', $settings->get('string_abc'));

        $settings->set('int_10', 10);
        $this->assertEquals(10, $settings->get('int_10'));

        $settings->set('array', [1, 2, "text"]);
        $this->assertEquals([1, 2, "text"], $settings->get('array'));

        $c = new TestClass( "John" );
        $settings->set( 'object', $c );
        $this->assertEquals( $c, $settings->get('object') );
    }


    public function testDefaultValue()
    {
        /** @var SettingsManager $settings */
        $settings = $this->get( 'trinity.settings' );

        $settings->setDefault( 'default_value', 'hello' );
        $this->assertEquals( 'hello', $settings->get('default_value') );

        $settings->set( 'default_value', 'new value' );
        $this->assertEquals( 'new value', $settings->get('default_value') );
    }

}


class TestClass{
    protected $name;

    /**
     * TestClass constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }
}