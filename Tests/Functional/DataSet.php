<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Tests\Functional;


use Doctrine\ORM\EntityManager;
use Faker\Factory;
use Trinity\Bundle\SettingsBundle\Entity\Setting;


/**
 * Class DataSet
 * @package Trinity\Bundle\SearchBundle\Tests\Functional
 */
class DataSet
{

    public function load(EntityManager $entityManager){

        $faker = Factory::create();;

        $count = 5;

        for($i = 0; $i < $count; $i++) {
            $setting = new Setting();
            $setting->setName($faker->unique()->word);
            $setting->setValue($faker->word);
            $entityManager->persist($setting);
        }

        $entityManager->flush();
    }

}