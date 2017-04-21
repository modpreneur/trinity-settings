<?php

namespace Tests\DependencyInjection;

use Tests\BaseTest;
use Trinity\Bundle\SettingsBundle\DependencyInjection\TrinitySettingsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ConfigurationTest
 * @package Tests
 */
class ConfigurationTest extends BaseTest
{

    /**
     * @dataProvider configurationDataProvider
     */
    public function testConfiguration($configs)
    {
        $loader = new TrinitySettingsExtension();

        $container = new ContainerBuilder();
        $loader->load($configs, $container);

        $parameterBag = $container->getParameter('settings_manager.settings');

        if (array_key_exists('null_value', $configs[0]['settings'])) {
            $this->assertEquals($configs[0]['settings']['null_value'], $parameterBag['null_value']);
            $this->assertEquals($configs[0]['settings']['key'], $parameterBag['key']);
            $this->assertEquals($configs[0]['settings']['group.key'], $parameterBag['group.key']);
        } else {
            $this->assertEmpty($parameterBag);
        }
    }


    public function configurationDataProvider()
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
