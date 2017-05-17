<?php

namespace Trinity\Bundle\SettingsBundle\Tests\DependencyInjection;

use Trinity\Bundle\SettingsBundle\Tests\BaseTest;
use Trinity\Bundle\SettingsBundle\DependencyInjection\TrinitySettingsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ConfigurationTest
 * @package Trinity\Bundle\SettingsBundle\Tests
 */
class ConfigurationTest extends BaseTest
{
    /**
     * @dataProvider configurationDataProvider
     *
     * @param array $configs
     */
    public function testConfiguration(array $configs)
    {
        $loader = new TrinitySettingsExtension();

        $container = new ContainerBuilder();
        $loader->load($configs, $container);

        $parameterBag = $container->getParameter('settings_manager.settings');

        if (\array_key_exists('null_value', $configs[0]['settings'])) {
            static::assertEquals($configs[0]['settings']['null_value'], $parameterBag['null_value']);
            static::assertEquals($configs[0]['settings']['key'], $parameterBag['key']);
            static::assertEquals($configs[0]['settings']['group.key'], $parameterBag['group.key']);
        } else {
            static::assertEmpty($parameterBag);
        }
    }

    /**
     * @return array
     */
    public function configurationDataProvider(): array
    {
        return [
            [
                [
                    [
                        'settings' => [
                            'null_value' => '~',
                            'key' => 'testKey',
                            'group.key' => 'testGroupKey'
                        ]
                    ]
                ]
            ]
            ,
            [
                [
                    [
                        'settings' => [
                            'null_value' => '~',
                            'key' => 'default',
                            'group.key' => 'default'
                        ]
                    ]
                ]

            ],
            [
                [
                    [
                        'settings' => [
                            'null_value' => 'null',
                            'key' => 'null',
                            'group.key' => 'null'
                        ]
                    ]
                ]
            ],
            [
                [
                    [
                        'settings' => [

                        ]

                    ]
                ]
            ]
        ];
    }
}
