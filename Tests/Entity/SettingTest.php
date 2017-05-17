<?php

namespace Trinity\Bundle\SettingsBundle\Tests\Entity;

use Trinity\Bundle\SettingsBundle\Entity\Setting;
use Trinity\Bundle\SettingsBundle\Tests\BaseTest;
use Trinity\Bundle\SettingsBundle\Tests\Owner;

/**
 * Class SettingTest
 * @package Trinity\Bundle\SettingsBundle\Tests
 */
class SettingTest extends BaseTest
{

    public function testEntity()
    {
        $owner = $this->getMockBuilder(Owner::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        // Set up the expectation for the update() method
        // to be called only once and with the string 'something'
        // as its parameter.
        $owner->expects(static::any())
            ->method('getId')
            ->will(static::returnValue(1));


        $settingsEntity = new Setting();

        static::assertNull($settingsEntity->getId());

        $settingsEntity->setName('name');
        static::assertEquals('name', $settingsEntity->getName());

        $settingsEntity->setValue('value');
        static::assertEquals('value', $settingsEntity->getValue());

        $settingsEntity->setGroup('group');
        static::assertEquals('group', $settingsEntity->getGroup());

        $settingsEntity->setOwnerId(1);
        static::assertEquals(1, $settingsEntity->getOwnerId());

        $settingsEntity->setOwner(2);
        static::assertEquals(2, $settingsEntity->getOwnerId());

        $settingsEntity->setOwner($owner);
        static::assertEquals(1, $settingsEntity->getOwnerId());

        $settingsEntity->setIsPrivate(false);
        static::assertFalse($settingsEntity->isIsPrivate());
    }
}
