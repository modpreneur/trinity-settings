<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Tests\Functional;


use Trinity\Bundle\SettingsBundle\Entity\Setting;


/**
 * Class ProductTest
 * @package Trinity\Bundle\SearchBundle\Tests\Functional
 */
class SettingTest extends WebTestCase
{


    public function getAllSettings()
    {
        $repository = $this
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository('SettingsBundle:Setting');

        $settings = $repository
            ->findAll();

        return $settings;
    }

    public function testAllSettings()
    {
        $settings = $this->getAllSettings();
        dump($settings);
//        $rows = [];
//
//        /**
//         * @var Product[] $products
//         */
//        foreach ($products as $product) {
//            $rows[] = [
//                'id' => $product->getId(),
//                'name' => $product->getName(),
//            ];
//        }
//
//        $this->assertEquals(
//            $this->toJson($rows),
//            $this->table('product')
//        );

    }





}