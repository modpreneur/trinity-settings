<?php

namespace Trinity\Bundle\SettingsBundle\Tests\Twig;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\ORM\EntityRepository;
use Trinity\Bundle\SettingsBundle\Entity\Setting;
use Trinity\Bundle\SettingsBundle\Manager\SettingsManager;
use Trinity\Bundle\SettingsBundle\Twig\SettingsExtension;
use Trinity\Bundle\SettingsBundle\Tests\BaseTest;

/**
 * Class SettingsExtensionTest
 * @package Trinity\Bundle\SettingsBundle\Tests
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
            ->will(static::returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn(static::returnValue($settingsEntity));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            [
                'parameter' => 'value'
            ],
            new ApcuCache()
        );

        $extension = new SettingsExtension($settings);

        static::assertEquals('settings_extension', $extension->getName());
        static::assertContainsOnlyInstancesOf(\Twig_SimpleFunction::class, $extension->getFunctions());
        static::assertEquals('value', $extension->getSetting('parameter'));
        static::assertTrue($extension->hasSetting('parameter'));
        static::assertTrue($extension->hasSettingValue('parameter', 'value'));
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
            ->will(static::returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn(static::returnValue($settingsEntity));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            [
                'parameter' => 'value'
            ],
            new ApcuCache()
        );

        $extension = new SettingsExtension($settings);

        static::assertNull($extension->getSetting('foo'));
    }
}
