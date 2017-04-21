<?php

namespace Tests\Twig;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\ORM\EntityRepository;
use Trinity\Bundle\SettingsBundle\Entity\Setting;
use Trinity\Bundle\SettingsBundle\Manager\SettingsManager;
use Trinity\Bundle\SettingsBundle\Twig\SettingsExtension;
use Tests\BaseTest;

/**
 * Class SettingsExtensionTest
 * @package Tests
 */
class SettingsExtensionTest extends BaseTest
{

    public function testSettingsExtension()
    {
        // Mocking
        $settingsEntity = new Setting();
        $settingsEntity->setName('parameter');
        $settingsEntity->setValue('value');

        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsRepository
            ->method('findBy')
            ->will($this->returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn($this->returnValue($settingsEntity));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            [
                'parameter' => 'value'
            ],
            new ApcuCache()
        );

        $extension = new SettingsExtension($settings);

        $this->assertEquals('settings_extension', $extension->getName());
        $this->assertContainsOnlyInstancesOf(\Twig_SimpleFunction::class, $extension->getFunctions());
        $this->assertEquals('value', $extension->getSetting('parameter'));
        $this->assertTrue($extension->hasSetting('parameter'));
        $this->assertTrue($extension->hasSettingValue('parameter', 'value'));
    }


    public function testSettingsExtensionGetExceptionCatchedAndSetToNull()
    {
        // Mocking
        $settingsEntity = new Setting();
        $settingsEntity->setName('parameter');
        $settingsEntity->setValue('value');

        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsRepository
            ->method('findBy')
            ->will($this->returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn($this->returnValue($settingsEntity));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            [
                'parameter' => 'value'
            ],
            new ApcuCache()
        );

        $extension = new SettingsExtension($settings);

        $this->assertNull($extension->getSetting('foo'));
    }
}
