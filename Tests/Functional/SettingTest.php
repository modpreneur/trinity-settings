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

        $settings->clear();

        $settings->setMany([
            'a' => 1,
            'b' => 2
        ]);

        $this->assertEquals(1, $settings->get('a'));
        $this->assertEquals(2, $settings->get('b'));

        $this->assertEquals([
            'a' => 1,
            'b' => 2
        ], $settings->all());

        $settings->clear();
        $this->assertEquals([], $settings->all());

        $settings->set('a', 1, 1);
        $settings->set('a', 2, 2);

        $this->assertEquals(1, $settings->get('a', 1));
        $this->assertEquals(2, $settings->get('a', 2));

        $settings->set('same', 1);
        $this->assertEquals(1, $settings->get('same'));

        $settings->set('same', 2);
        $this->assertEquals(2, $settings->get('same'));

        $settings->set('same', 2, 1);
        $this->assertEquals(2, $settings->get('same', 1));

        $settings->set('same', 2, 2);
        $this->assertEquals(2, $settings->get('same', 2));
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