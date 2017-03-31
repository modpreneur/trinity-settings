<?php

namespace Tests\Unit;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Trinity\Bundle\SettingsBundle\Entity\Setting;
use Trinity\Bundle\SettingsBundle\Manager\SettingsManager;

/**
 * Class SettingsTest
 * @package Tests
 */
class SettingsTest extends TestCase
{
    /**
     * @return Registry
     */
    private function getRegistry($settingsRepository)
    {
        $registry = $this
            ->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager->method('getRepository')
            ->willReturn($settingsRepository);

        $registry->method('getManager')
            ->will($this->returnValue($entityManager));

        return $registry;
    }


    /**
     * Return user id. $this->getUser()->getId();
     * @return int
     */
    private function getUserId(): int
    {
        return 1;
    }


    public function testEntity()
    {
        $settingsEntity = new Setting();

        $settingsEntity->setName('name');
        $this->assertEquals('name', $settingsEntity->getName());

        $settingsEntity->setValue('value');
        $this->assertEquals('value', $settingsEntity->getValue());

        $settingsEntity->setGroup('group');
        $this->assertEquals('group', $settingsEntity->getGroup());

        $settingsEntity->setOwnerId(1);
        $this->assertEquals(1, $settingsEntity->getOwnerId());

        $settingsEntity->setOwner(1);
        $this->assertEquals(1, $settingsEntity->getOwnerId());

        $settingsEntity->setIsPrivate(false);
        $this->assertFalse($settingsEntity->isIsPrivate());
    }


    public function testSetValueToEntity() : void
    {
        $settingsEntity = new Setting();

        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $settingsRepository
            ->method('findOneBy')
            ->will($this->returnValue($settingsEntity));

        $settings = new SettingsManager($this->getRegistry($settingsRepository), ['user.profileUrl']);
        $settings->set('profileUrl', 'https://example.php/image.jpg', $this->getUserId(), 'user');

        // tests

        $this->assertEquals('profileUrl_1', $settingsEntity->getName());
        $this->assertEquals('https://example.php/image.jpg', $settingsEntity->getValue());
        $this->assertEquals($this->getUserId(), $settingsEntity->getOwnerId());
        $this->assertEquals('user', $settingsEntity->getGroup());

        /* array as value */
        $settings->set('profileUrl', ['x', 'y', 'z'], $this->getUserId(), 'user');
        $this->assertEquals(['x', 'y', 'z'], $settingsEntity->getValue());

        // without group
        $settings->set('profileUrl', 'value', $this->getUserId());
        $this->assertNull($settingsEntity->getGroup());

        // without group and userId
        $settings->set('profileUrl', 'value');
        $this->assertNull($settingsEntity->getOwnerId());
    }


    /**
     * @expectedException \Trinity\Bundle\SettingsBundle\Exception\PropertyNotExistsException
     * @expectedExceptionMessage Property 'xxx' doesn't exists. Available properties are profileUrl.
     */
    public function testGetValueWithNoExistsProperty()
    {
        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $settingsRepository
            ->method('findOneBy')
            ->will($this->returnValue([]));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settings = new SettingsManager($this->getRegistry($settingsRepository), ['profileUrl' => 'defaultValue']);

        /* Property must be defined.*/
        $settings->get('xxx');
    }


    /**
     * @expectedException \Trinity\Bundle\SettingsBundle\Exception\PropertyNotExistsException
     * @expectedExceptionMessage Property 'profilUrl' doesn't exists. Did you mean profileUrl. Available properties are profileUrl.
     */
    public function testGetValueWithTypeErrorProperty()
    {
        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $settingsRepository
            ->method('findOneBy')
            ->will($this->returnValue([]));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settings = new SettingsManager($this->getRegistry($settingsRepository), ['profileUrl' => 'defaultValue']);

        /* Property must be defined.*/
        $settings->get('profilUrl');
    }


    public function testGetValueWithDefaultValue()
    {
        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $settingsRepository
            ->method('findOneBy')
            ->will($this->returnValue([]));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settings = new SettingsManager($this->getRegistry($settingsRepository), ['profileUrl' => 'defaultValue']);
        $this->assertEquals('defaultValue', $settings->get('profileUrl'));

        // default value for user
        $this->assertEquals('defaultValue', $settings->get('profileUrl', 1));

        // group with default value
        $settings = new SettingsManager($this->getRegistry($settingsRepository), ['user.profileUrl' => 'defaultValue']);
        $this->assertEquals('defaultValue', $settings->get('profileUrl', 1, 'user'));
    }


    public function testGetValue()
    {
        $settingsEntity = new Setting();
        $settingsEntity->setName('parameter');
        $settingsEntity->setValue('value');

        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $settingsRepository
            ->method('findOneBy')
            ->will($this->returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'defaultValue'],
            new ApcuCache()
        );

        $settings->set('parameter', 'tom'); // apcu

        $this->assertEquals('tom', $settings->get('parameter'));
        sleep(2);
        $this->assertEquals('tom', $settings->get('parameter'));
    }
}
