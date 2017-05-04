<?php

namespace Tests\DependencyInjection;

use Trinity\Bundle\SettingsBundle\DependencyInjection\TrinitySettingsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Trinity\Bundle\SettingsBundle\SettingsBundle;
use Tests\BaseTest;

/**
 * Class TrinitySettingsExtensionTest
 * @package Tests
 */
class TrinitySettingsExtensionTest extends BaseTest
{

    public function testSettingBundle()
    {
        $container = new ContainerBuilder();
        $settingBundle = new SettingsBundle();

        $settingBundle->build($container);

        $extensions = $container->getExtensions();

        static::assertInstanceOf(TrinitySettingsExtension::class, $extensions['trinity_settings']);

        static::assertInstanceOf(TrinitySettingsExtension::class, $container->getExtension('trinity_settings'));

        static::assertTrue($container->hasExtension('trinity_settings'));
    }
}
