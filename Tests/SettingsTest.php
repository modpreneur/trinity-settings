<?php

namespace Tests;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Trinity\Bundle\SettingsBundle\DependencyInjection\TrinitySettingsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Trinity\Bundle\SettingsBundle\Entity\Setting;
use Trinity\Bundle\SettingsBundle\Manager\SettingsManager;
use Trinity\Bundle\SettingsBundle\Twig\SettingsExtension;
use Trinity\Bundle\SettingsBundle\SettingsBundle;

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


    public function testSetValueAndGet()
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
            ->method('findOneBy')
            ->will($this->returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['height' => 'default'],
            new ApcuCache()
        );


        $settings->set('height', '67cm'); // apcu

        // test

        $this->assertEquals('67cm', $settings->get('height'));
    }


    public function testGetDefaultValue()
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
            ->method('findOneBy')
            ->will($this->returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);


        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['height' => 'default'],
            new ApcuCache()
        );
        $settings->setDefault('height', 'default');

        $this->assertEquals('default', $settings->get('height'));
    }


    public function testSetDefaultValueSetValueAndClear()
    {
        // Mocking
        $settingsEntity = new Setting();
        $settingsEntity->setName('parameter');
        $settingsEntity->setValue('value');
        $settingsEntity->setOwner($this->getUserId());
        $settingsEntity->setGroup('testingGroup');

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

        $settingsRepository
            ->method('findBy')
            ->will($this->returnValue($settingsEntity));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'default'],
            new ApcuCache()
        );

        $this->assertEquals('value', $settings->get('parameter', $this->getUserId()));

        $settings->clear($this->getUserId(), 'testingGroup');
        $settings->setDefault('height', 'default');

        $this->assertEquals('default', $settings->get('height', $this->getUserId()));

        $settings->set('height', '67cm', $this->getUserId(), 'testingGroup'); // apcu

        $this->assertEquals('67cm', $settings->get('height', $this->getUserId()));

        $settings->clear($this->getUserId(), 'testingGroup');

        $this->assertEquals('default', $settings->get('height'));
    }


    public function testHasWithOutGroup()
    {
        // Mocking
        $settingsEntity = new Setting();
        $settingsEntity->setName('parameter');
        $settingsEntity->setValue('value');
        $settingsEntity->setOwner($this->getUserId());
        $settingsEntity->setGroup('testingGroup');

        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsRepository
            ->method('findOneBy')
            ->with($this->equalTo(['name' => 'foo']))
            ->will($this->returnValue(null));

        $settingsRepository
            ->method('findOneBy')
            ->will($this->returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'default'],
            new ApcuCache()
        );


        $settings->set('height', '67cm', $this->getUserId(), 'testingGroup'); // apcu

        //tests

        /* has for existing setting */
        $this->assertTrue($settings->has('height', $this->getUserId()));
        $this->assertTrue($settings->has('height', $this->getUserId(), 'testingGroup'));

        /* has for not existing setting */
        $this->assertFalse($settings->has('foo', $this->getUserId()));
    }


    public function testSetCacheProvider()
    {
        // Mocking
        $settingsEntity = new Setting();
        $settingsEntity->setName('parameter');
        $settingsEntity->setValue('value');
        $settingsEntity->setOwner($this->getUserId());
        $settingsEntity->setGroup('testingGroup');

        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'default']
        );

        /* getCacheProvider for SettingsManager where is not defined */
        $this->assertNull($settings->getCacheProvider());


        $settings->setCacheProvider(new ApcuCache());

        /* getCacheProvider for SettingsManager where is set cacheProvider */
        $this->assertInstanceOf(ApcuCache::class, $settings->getCacheProvider());

    }


    /**
     * @dataProvider settingProvider
     */
    public function testSetWeirdData($name, $value, $owner = null, $group = null)
    {
        // Mocking
        $settingsEntity = new Setting();
        $settingsEntity->setName($name);
        $settingsEntity->setValue($value);
        !$owner ?: $settingsEntity->setOwner($owner);
        !$group ?: $settingsEntity->setGroup($group);

        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsRepository
            ->method('findOneBy')
            ->will($this->returnValue($settingsEntity));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'default']
        );

        $settings->set($name, $value, $owner, $group);

        //tests

        /* conditions for settingProvider count of arguments */
        if(isset($group)){
            $this->assertEquals($value, $settings->get($name, $owner, $group));

        } elseif (isset($owner)) {
            $this->assertEquals($value, $settings->get($name, $owner));
        } else {
            $this->assertEquals($value, $settings->get($name));
        }
    }


    public function settingProvider()
    {
        return [
            ['parameter1', '1 + 2', 1, 'testingGroup'],
            ['parameter2', "value", 987654, 'testingGroup'],
            ['parameter3', '"z\\/*/\\??$$#&&"', 2345, 'testingGroup'],
            ['parameter4', 'gdfgd', 52345234, 'testingGroup'],
            ['parameter5', 'sgd', 53425324, 'testingGroup'],
            ['parameter6', 'gsdg', 3453453 , null],
            ['parameter7', 'gsdf', null, null]
        ];
    }


    public function testAllEmpty()
    {
        // Mocking
        $settingsRepository = $this
        ->getMockBuilder(EntityRepository::class)
        ->disableOriginalConstructor()
        ->getMock();

        $settingsRepository
            ->method('findBy')
            ->will($this->returnValue([]));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settings = new SettingsManager($this->getRegistry($settingsRepository), ['profileUrl' => 'defaultValue']);

        /* geting all settings without setup any setting */

        //parameterless
        $this->assertEmpty($settings->all());

        //with arguments
        $this->assertEmpty($settings->all('xxx'));
        $this->assertEmpty($settings->all( null, 'yyy'));
        $this->assertEmpty($settings->all('xxx', 'yyy'));
    }


    /**
     * @dataProvider settingManyProvider
     */
    public function testSetMany($paramenters, $owner = null, $group = null)
    {
        // Mocking
        $all = [];

        /* Setting up mocking Entity from array $paramenters */

        foreach ($paramenters as $key => $value) {
            $settingsEntity = new Setting();
            $settingsEntity->setName($key);
            $settingsEntity->setValue($value);
            !$owner ?: $settingsEntity->setOwner($owner);
            !$group ?: $settingsEntity->setGroup($group);
            array_push($all, $settingsEntity);
        }
        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsRepository
            ->method('findBy')
            ->will($this->returnValue($all));

        $settingsRepository
            ->method('findAll')
            ->willReturn($this->returnValue($all));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'default'],
            new ApcuCache()
        );

        $settings->setMany($paramenters, $owner, $group);

        $allGhost = $settings->all($owner, $group);

        $index = 0;

        foreach ($allGhost as $key => $value) {


            $this->assertTrue(array_key_exists ( $all[$index]->getName() , $allGhost ));
            //check if is correctly set
            $this->assertEquals($all[$index]->getValue(), $allGhost[$all[$index]->getName()]);

            //check if is all $paramenters was correctly transformet to Setting::class
            $this->assertInstanceOf(Setting::class, $all[$index]);
            $index++;
        }
    }


    public function settingManyProvider()
    {
        return [
            [
                [
                    'paramameter1' => 'value1',
                    'paramameter2' => 'value2',
                    'paramameter3' => 'value3',
                    'paramameter4' => 'value4',
                    'paramameter5' => 'value5',
                ],
                1,
                'testingGroup'
            ],
            [
                [
                    'paramameter6' => 'value6',
                    'paramameter7' => 'value7',
                    'paramameter8' => 'value8',
                    'paramameter9' => 'value9',
                    'paramameter10' => 'value10',
                ],
                -45678,
                'randomWord'
            ],
            [
                [
                    'paramameter1' => 'value1',
                    'paramameter2' => 'value2',
                    'paramameter3' => 'value3',
                    'paramameter4' => 'value4',
                    'paramameter5' => 'value5',
                ],
                134567,
                'randomWord'
            ],
        ];
    }


    /**
     * @dataProvider getSuggestionProvider
     */
    public function testGetSuggestion($value)
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
                'parameter' => 'value',
                'height' => '150cm',
                'width' => '200cm',
                'water' => 'weet',
                'sand' => 'dry',
            ],
            new ApcuCache()
        );

        $settings->set('parameter', 'value', $this->getUserId(), 'testingGroup');
        $this->assertNotEmpty($settings->getSuggestion($value));
        $this->assertTrue(in_array('parameter', $settings->getSuggestion($value)));
        $neco = $settings->getSuggestion($value);
        $this->assertInternalType('string',$settings->get($neco[0], $this->getUserId(), 'testingGroup'));
    }


    public function getSuggestionProvider()
    {
        return [
            ['parameter1'],
            ['parametar'],
            ['pamreter'],
            ['prameter'],
            ['parakoter']
        ];
    }


    /**
     * @expectedException \Trinity\Bundle\SettingsBundle\Exception\PropertyNotExistsException
     * @expectedExceptionMessage Property 'parameter1' doesn't exists. Did you mean parameter. Available properties are parameter.
     */
    public function testCacheProviderClearSetGet()
    {
        // Mocking
        $settingsEntity = new Setting();
        $settingsEntity->setName('parameter1');
        $settingsEntity->setValue('value2');

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
                'parameter' => 'value',
            ]
        );

        $this->assertNull($settings->getCacheProvider());

        $settings->setCacheProvider(new ApcuCache());

        $this->assertInstanceOf(ApcuCache::class, $settings->getCacheProvider());

        $settings->set('parameter1', 'value1');

        $this->assertEquals('value1', $settings->get('parameter1'));

        $settings->clearCacheProvider();


        /* Property parameter1 was clear */
        $this->assertNull($settings->get('parameter1'));

    }


    /**
     * @dataProvider setDataWithNullDefaultValueProvider
     */
    public function testSetDataWithNullDefaultValue($name, $value, $owner = null, $group = null)
    {
        // Mocking
        $settingsEntity = new Setting();
        $settingsEntity->setName($name);
        $settingsEntity->setValue(null);
        !$owner ?: $settingsEntity->setOwner($owner);
        !$group ?: $settingsEntity->setGroup($group);

        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsRepository
            ->method('findOneBy')
            ->will($this->returnValue($settingsEntity));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            [$name => null]
        );

       $settings->setDefault($name, null);

        $settings->set($name, $value, $owner, $group);

        //tests

        /* conditions for settingProvider count of arguments */
        if(isset($group)){
            $this->assertEquals(null, $settings->get($name, $owner, $group));

        } elseif (isset($owner)) {
            $this->assertEquals(null, $settings->get($name, $owner));
        } else {
            $this->assertEquals(null, $settings->get($name));
        }
    }


    public function setDataWithNullDefaultValueProvider()
    {
        return [
            ['parameter1', '1 + 2', 1, 'testingGroup'],
            ['parameter2', 'foo', 1, 'testingGroup'],
            ['parameter3', '43cm', 1, 'foo']
        ];
    }


    public function testGetValueFailDoctrine()
    {
        // Mocking
        $settingsEntity = new Setting();
        $settingsEntity->setName('parameter');
        $settingsEntity->setValue('value');
        $settingsEntity->setOwner($this->getUserId());
        $settingsEntity->setGroup('testingGroup');


        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsRepository
            ->method('findOneBy')
            ->will($this->throwException(new \Exception));

        $settingsRepository
            ->method('findAll')
            ->will($this->throwException(new \Exception));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'default']
        );

        $settings->setDefault('parameter_1', 'default');
        $settings->set('parameter', 'foo', $this->getUserId(), 'testingGroup'); // apcu

        $this->assertEquals('default', $settings->get('parameter', 1, 'testingGroup'));
    }


    public function testClear()
    {
        // Mocking
        $settingsEntity = new Setting();
        $settingsEntity->setName('parameter');
        $settingsEntity->setValue('value');
        $settingsEntity->setOwner($this->getUserId());
        $settingsEntity->setGroup('testingGroup');

        $settingsEntity2 = new Setting();
        $settingsEntity2->setName('parameter');
        $settingsEntity2->setValue('value');
        $settingsEntity2->setOwner($this->getUserId());
        $settingsEntity2->setGroup('testingGroup');

        $row = [$settingsEntity, $settingsEntity2];

        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $settingsRepository
            ->method('findOneBy')
            ->will($this->returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn($row);

        $settingsRepository
            ->method('findBy')
            ->willReturn($row);

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'default']
        );

        $settings->setDefault('height', 'default');

        $this->assertEquals('default', $settings->get('height', $this->getUserId()));

        $settings->set('height', '67cm', $this->getUserId(), 'testingGroup'); // apcu

        $this->assertEquals('67cm', $settings->get('height', null, 'testingGroup'));

        $settings->clear($this->getUserId(), 'testingGroup');

        $this->assertEquals('67cm', $settings->get('height'));
    }


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


    /**
     * @dataProvider configurationDataProvider
     */
    public function testConfiguration($configs)
    {
        $loader = new TrinitySettingsExtension();

        $container = new ContainerBuilder();
        $loader->load($configs, $container);

        $parameterBag = $container->getParameter('settings_manager.settings');

        $first = array_key_exists('null_value', $configs[0]['settings']) ? $configs[0]['settings']['null_value'] : null;

        $second = array_key_exists('null_value', $configs[0]['settings']) ? $configs[0]['settings']['key'] : null;

        $third = array_key_exists('null_value', $configs[0]['settings']) ? $configs[0]['settings']['group.key'] : null;

        if(array_key_exists('null_value', $configs[0]['settings'])){
            $this->assertEquals($first, $parameterBag['null_value']);
            $this->assertEquals($second, $parameterBag['key']);
            $this->assertEquals($third, $parameterBag['group.key']);
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


    public function testSettingBundle()
    {
        $container = new ContainerBuilder();
        $settingBundle = new SettingsBundle();

        $settingBundle->build($container);

        $extensions = $container->getExtensions();

        $this->assertInstanceOf(TrinitySettingsExtension::class , $extensions['trinity_settings']);
        $this->assertInstanceOf(TrinitySettingsExtension::class , $container->getExtension('trinity_settings'));
        $this->assertTrue($container->hasExtension('trinity_settings'));

    }
}
