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

        // settings.defaults:
        $this->assertEquals('kure', $settings->get('sefik'));
        $this->assertEquals(null, $settings->get('null_value'));

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

        $settings->set('null', 'not-null');
        $this->assertEquals('not-null', $settings->get('null'));

        $settings->set('null', null);
        $this->assertEquals(null, $settings->get('null'));


        $this->assertEquals(false, $settings->has('1111'));
        $this->assertEquals(true, $settings->has('null'));

        $settings->set('v', false);
        $this->assertEquals(true, $settings->has('v'));

        $settings->set('v', true);
        $this->assertEquals(true, $settings->has('v'));


        $settings->set('g', 'value', null, 'g');
        $this->assertEquals(true, $settings->has('g', null, 'g'));
        $this->assertEquals('value', $settings->get('g', null, 'g'));

        $settings->set('gg', 'value', null, 'g');
        $this->assertEquals(true, $settings->has('gg', null, 'g'));
        $this->assertEquals('value', $settings->get('gg', null, 'g'));
    }


    /**
     * @expectedException \Trinity\Bundle\SettingsBundle\Exception\PropertyNotExistsException
     * @expectedExceptionMessage Property 'not-exists-value' doesn't exists.
     */
    public function testExceptionForNonExistsValue(){
        /** @var SettingsManager $settings */
        $settings = $this->get( 'trinity.settings' );

        $settings->get('not-exists-value');
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