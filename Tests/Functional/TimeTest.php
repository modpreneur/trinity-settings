<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Tests\Functional;


use Trinity\Bundle\SettingsBundle\Manager\SettingsManager;


class TimeTest extends WebTestCase
{

    function  testSpeed(){

        /** @var SettingsManager $settings */
        $settings = $this->get('trinity.settings');

        dump('Cache:');
        dump('Start - write');
        $s = microtime(true);

        for($i = 0; $i < 5000; $i++){
            $settings->set('i_'. $i, $i);
        }

        $e = microtime(true);
        dump('End - write');
        dump($e-$s);

        dump('Start - read');
        $s = microtime(true);

        for($i = 0; $i < 5000; $i++){
            $settings->get('i_'. $i);
        }

        $e = microtime(true);
        dump('End - read');
        dump($e-$s);

        dump('Without cache:');
        $settings->setCacheProvider(null);
        dump('Start - write');
        $s = microtime(true);

        for($i = 0; $i < 10000; $i++){
            $settings->set('i_'. $i, $i);
        }

        $e = microtime(true);
        dump('End - write');
        dump($e-$s);

        dump('Start - read');
        $s = microtime(true);

        for($i = 0; $i < 10000; $i++){
            $settings->get('i_'. $i);
        }

        $e = microtime(true);
        dump('End - read');
        dump($e-$s);
    }


}