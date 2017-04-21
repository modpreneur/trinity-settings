<?php

namespace Tests\Entity;

use Trinity\Bundle\SettingsBundle\Entity\Setting;
use Tests\BaseTest;
use Tests\Owner;

/**
 * Class SettingTest
 * @package Tests
 */
class SettingTest extends BaseTest
{

    public function testEntity()
    {
        $owner = $this->getMockBuilder(Owner::class)
            ->setMethods(['getId'])
            ->getMock();

        // Set up the expectation for the update() method
        // to be called only once and with the string 'something'
        // as its parameter.
        $owner->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));


        $settingsEntity = new Setting();

        $this->assertNull($settingsEntity->getId());

        $settingsEntity->setName('name');
        $this->assertEquals('name', $settingsEntity->getName());

        $settingsEntity->setValue('value');
        $this->assertEquals('value', $settingsEntity->getValue());

        $settingsEntity->setGroup('group');
        $this->assertEquals('group', $settingsEntity->getGroup());

        $settingsEntity->setOwnerId(1);
        $this->assertEquals(1, $settingsEntity->getOwnerId());

        $settingsEntity->setOwner(2);
        $this->assertEquals(2, $settingsEntity->getOwnerId());

        $settingsEntity->setOwner($owner);
        $this->assertEquals(1, $settingsEntity->getOwnerId());

        $settingsEntity->setIsPrivate(false);
        $this->assertFalse($settingsEntity->isIsPrivate());
    }
}
