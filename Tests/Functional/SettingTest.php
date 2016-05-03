<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Tests\Functional;

use Trinity\Bundle\SettingsBundle\Exception\PropertyNotExistsException;
use Trinity\Bundle\SettingsBundle\Manager\SettingsManager;

/**
 * Class ProductTest
 * @package Trinity\Bundle\SearchBundle\Tests\Functional
 */
class SettingTest extends WebTestCase
{

    public function testSetValue()
    {
        /** @var SettingsManager $settings */
        $settings = $this->get('trinity.settings');

        // settings.defaults:
        static::assertEquals('kure', $settings->get('sefik'));
        static::assertEquals(null, $settings->get('null_value'));

        $settings->set('string_abc', 'abc');
        static::assertEquals('abc', $settings->get('string_abc'));

        $settings->set('int_10', 10);
        static::assertEquals(10, $settings->get('int_10'));

        $settings->set('array', [1, 2, 'text']);
        static::assertEquals([1, 2, 'text'], $settings->get('array'));

        $c = new TestClass('John');
        $settings->set('object', $c);
        static::assertEquals($c, $settings->get('object'));

        $settings->clear();

        $settings->setMany([
            'a' => 1,
            'b' => 2
        ]);

        static::assertEquals(1, $settings->get('a'));
        static::assertEquals(2, $settings->get('b'));

        static::assertEquals([
            'a' => 1,
            'b' => 2
        ], $settings->all());

        $settings->clear();
        static::assertEquals([], $settings->all());

        $settings->set('a', 1, 1);
        $settings->set('a', 2, 2);

        static::assertEquals(1, $settings->get('a', 1));
        static::assertEquals(2, $settings->get('a', 2));

        $settings->set('same', 1);
        static::assertEquals(1, $settings->get('same'));

        $settings->set('same', 2);
        static::assertEquals(2, $settings->get('same'));

        $settings->set('same', 2, 1);
        static::assertEquals(2, $settings->get('same', 1));

        $settings->set('same', 2, 2);
        static::assertEquals(2, $settings->get('same', 2));

        $settings->set('null', 'not-null');
        static::assertEquals('not-null', $settings->get('null'));

        $settings->set('null', null);
        static::assertEquals(null, $settings->get('null'));


        static::assertEquals(false, $settings->has('1111'));
        static::assertEquals(true, $settings->has('null'));

        $settings->set('v', false);
        static::assertEquals(true, $settings->has('v'));

        $settings->set('v', true);
        static::assertEquals(true, $settings->has('v'));


        $settings->set('g', 'value', null, 'g');
        static::assertEquals(true, $settings->has('g', null, 'g'));
        static::assertEquals('value', $settings->get('g', null, 'g'));

        $settings->set('gg', 'value', null, 'g');
        static::assertEquals(true, $settings->has('gg', null, 'g'));
        static::assertEquals('value', $settings->get('gg', null, 'g'));

        static::assertEquals([ 'g' => 'value', 'gg' => 'value'], $settings->all(null, 'g'));


        static::assertEquals('value', $settings->get('key', null, 'group'));
    }


    /**
     * @expectedException \Trinity\Bundle\SettingsBundle\Exception\PropertyNotExistsException
     * @expectedExceptionMessage Property 'not-exists-value' doesn't exists.
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws PropertyNotExistsException
     */
    public function testExceptionForNonExistsValue()
    {
        /** @var SettingsManager $settings */
        $settings = $this->get('trinity.settings');
        $settings->get('not-exists-value');
    }


    public function testDefaultValue()
    {
        /** @var SettingsManager $settings */
        $settings = $this->get('trinity.settings');

        $settings->setDefault('default_value', 'hello');
        static::assertEquals('hello', $settings->get('default_value'));

        $settings->set('default_value', 'new value');
        static::assertEquals('new value', $settings->get('default_value'));
    }


    /**
     * @throws \Trinity\Bundle\SettingsBundle\Exception\PropertyNotExistsException
     *
     * @expectedException \Trinity\Bundle\SettingsBundle\Exception\PropertyNotExistsException
     * @expectedExceptionMessage Property 'aac' doesn't exists. Did you mean aaa. Available properties are null_value, sefik, group.key, aaa, aab.
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testDidYouMeanSomething()
    {
        /** @var SettingsManager $settings */
        $settings = $this->get('trinity.settings');

        $settings->clear();
        $settings->set('aaa', 11);
        $settings->set('aab', 11);

        $settings->get('aac');
    }
}
