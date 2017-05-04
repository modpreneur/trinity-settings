<?php

namespace Tests\Manager;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\ORM\EntityRepository;
use Trinity\Bundle\SettingsBundle\Entity\Setting;
use Trinity\Bundle\SettingsBundle\Manager\SettingsManager;
use Tests\BaseTest;

/**
 * Class SettingsManagerTest
 * @package Tests
 */
class SettingsManagerTest extends BaseTest
{

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
            ->will(static::returnValue([]));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settings = new SettingsManager($this->getRegistry($settingsRepository), ['profileUrl' => 'defaultValue']);

        /* Property must be defined.*/
        $settings->get('xxx');
    }


    /**
     * @expectedException \Trinity\Bundle\SettingsBundle\Exception\PropertyNotExistsException
     * @expectedExceptionMessage
     * Property 'profilUrl' doesn't exists. Did you mean profileUrl. Available properties are profileUrl.
     */
    public function testGetValueWithTypeErrorProperty()
    {
        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $settingsRepository
            ->method('findOneBy')
            ->will(static::returnValue([]));

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
            ->will(static::returnValue([]));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settings = new SettingsManager($this->getRegistry($settingsRepository), ['profileUrl' => 'defaultValue']);
        static::assertEquals('defaultValue', $settings->get('profileUrl'));

        // default value for user
        static::assertEquals('defaultValue', $settings->get('profileUrl', 1));

        // group with default value
        $settings = new SettingsManager($this->getRegistry($settingsRepository), ['user.profileUrl' => 'defaultValue']);
        static::assertEquals('defaultValue', $settings->get('profileUrl', 1, 'user'));
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
            ->will(static::returnValue($settingsEntity));

        $settings = new SettingsManager($this->getRegistry($settingsRepository), ['user.profileUrl']);
        $settings->set('profileUrl', 'https://example.php/image.jpg', $this->getUserId(), 'user');

        static::assertEquals('profileUrl_1', $settingsEntity->getName());
        static::assertEquals('https://example.php/image.jpg', $settingsEntity->getValue());
        static::assertEquals($this->getUserId(), $settingsEntity->getOwnerId());
        static::assertEquals('user', $settingsEntity->getGroup());

        /* array as value */
        $settings->set('profileUrl', ['x', 'y', 'z'], $this->getUserId(), 'user');
        static::assertEquals(['x', 'y', 'z'], $settingsEntity->getValue());

        // without group
        $settings->set('profileUrl', 'value', $this->getUserId());
        static::assertNull($settingsEntity->getGroup());

        // without group and userId
        $settings->set('profileUrl', 'value');
        static::assertNull($settingsEntity->getOwnerId());
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
            ->will(static::returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'defaultValue'],
            new ApcuCache()
        );

        $settings->set('parameter', 'tom'); // apcu

        static::assertEquals('tom', $settings->get('parameter'));
        \sleep(2);
        static::assertEquals('tom', $settings->get('parameter'));
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
            ->will(static::returnValue($settingsEntity));

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

        static::assertEquals('67cm', $settings->get('height'));
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
            ->will(static::returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['height' => 'default'],
            new ApcuCache()
        );
        $settings->setDefault('height', 'default');

        static::assertEquals('default', $settings->get('height'));
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
            ->will(static::returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settingsRepository
            ->method('findBy')
            ->will(static::returnValue($settingsEntity));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'default'],
            new ApcuCache()
        );

        static::assertEquals('value', $settings->get('parameter', $this->getUserId()));

        $settings->clear($this->getUserId(), 'testingGroup');
        $settings->setDefault('height', 'default');

        static::assertEquals('default', $settings->get('height', $this->getUserId()));

        $settings->set('height', '67cm', $this->getUserId(), 'testingGroup'); // apcu

        static::assertEquals('67cm', $settings->get('height', $this->getUserId()));

        $settings->clear($this->getUserId(), 'testingGroup');

        static::assertEquals('default', $settings->get('height'));
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
            ->with(static::equalTo(['name' => 'foo']))
            ->will(static::returnValue(null));

        $settingsRepository
            ->method('findOneBy')
            ->will(static::returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'default'],
            new ApcuCache()
        );

        $settings->set('height', '67cm', $this->getUserId(), 'testingGroup'); // apcu

        /* has for existing setting */
        static::assertTrue($settings->has('height', $this->getUserId()));
        static::assertTrue($settings->has('height', $this->getUserId(), 'testingGroup'));

        /* has for not existing setting */
        static::assertFalse($settings->has('foo', $this->getUserId()));
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
        static::assertNull($settings->getCacheProvider());

        $settings->setCacheProvider(new ApcuCache());

        /* getCacheProvider for SettingsManager where is set cacheProvider */
        static::assertInstanceOf(ApcuCache::class, $settings->getCacheProvider());
    }

    /**
     * @dataProvider settingProvider
     *
     * @param $name
     * @param $value
     * @param null $owner
     * @param null $group
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
            ->will(static::returnValue($settingsEntity));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'default']
        );

        $settings->set($name, $value, $owner, $group);

        //tests

        /* conditions for settingProvider count of arguments */
        if (isset($group)) {
            static::assertEquals($value, $settings->get($name, $owner, $group));

        } elseif (isset($owner)) {
            static::assertEquals($value, $settings->get($name, $owner));
        } else {
            static::assertEquals($value, $settings->get($name));
        }
    }

    /**
     * @return array
     */
    public function settingProvider(): array
    {
        return [
            ['parameter1', '1 + 2', 1, 'testingGroup'],
            ['parameter2', 'value', 987654, 'testingGroup'],
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
            ->will(static::returnValue([]));

        $settingsRepository
            ->method('findAll')
            ->willReturn([]);

        $settings = new SettingsManager($this->getRegistry($settingsRepository), ['profileUrl' => 'defaultValue']);

        /* geting all settings without setup any setting */

        //parameterless
        static::assertEmpty($settings->all());

        //with arguments
        static::assertEmpty($settings->all('xxx'));
        static::assertEmpty($settings->all(null, 'yyy'));
        static::assertEmpty($settings->all('xxx', 'yyy'));
    }

    /**
     * @dataProvider settingManyProvider
     *
     * @param $parameters
     * @param null $owner
     * @param null $group
     */
    public function testSetMany($parameters, $owner = null, $group = null)
    {
        // Mocking
        $all = [];

        /* Setting up mocking Entity from array $parameters */

        foreach ($parameters as $key => $value) {
            $settingsEntity = new Setting();
            $settingsEntity->setName($key);
            $settingsEntity->setValue($value);
            !$owner ?: $settingsEntity->setOwner($owner);
            !$group ?: $settingsEntity->setGroup($group);
            $all[] = $settingsEntity;
        }
        $settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsRepository
            ->method('findBy')
            ->will(static::returnValue($all));

        $settingsRepository
            ->method('findAll')
            ->willReturn(static::returnValue($all));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'default'],
            new ApcuCache()
        );

        $settings->setMany($parameters, $owner, $group);

        $allGhost = $settings->all($owner, $group);

        $index = 0;

        foreach ($allGhost as $key => $value) {
            static::assertTrue(\array_key_exists($all[$index]->getName(), $allGhost));

            //check if is correctly set
            static::assertEquals($all[$index]->getValue(), $allGhost[$all[$index]->getName()]);

            //check if is all $paramenters was correctly transformet to Setting::class
            static::assertInstanceOf(Setting::class, $all[$index]);
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
     *
     * @param $value
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
            ->will(static::returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn(static::returnValue($settingsEntity));

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
        static::assertNotEmpty($settings->getSuggestion($value));
        static::assertTrue(\in_array('parameter', $settings->getSuggestion($value), true));
        $neco = $settings->getSuggestion($value);
        static::assertInternalType('string', $settings->get($neco[0], $this->getUserId(), 'testingGroup'));
    }

    /**
     * @return array
     */
    public function getSuggestionProvider(): array
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
     * @expectedExceptionMessage
     * Property 'parameter1' doesn't exists. Did you mean parameter. Available properties are parameter.
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
            ->will(static::returnValue($settingsEntity));

        $settingsRepository
            ->method('findAll')
            ->willReturn(static::returnValue($settingsEntity));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            [
                'parameter' => 'value',
            ]
        );

        static::assertNull($settings->getCacheProvider());

        $settings->setCacheProvider(new ApcuCache());

        static::assertInstanceOf(ApcuCache::class, $settings->getCacheProvider());

        $settings->set('parameter1', 'value1');

        static::assertEquals('value1', $settings->get('parameter1'));

        $settings->clearCacheProvider();


        /* Property parameter1 was clear */
        static::assertNull($settings->get('parameter1'));
    }

    /**
     * @dataProvider setDataWithNullDefaultValueProvider
     *
     * @param $name
     * @param $value
     * @param null $owner
     * @param null $group
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
            ->will(static::returnValue($settingsEntity));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            [$name => null]
        );

        $settings->setDefault($name, null);

        $settings->set($name, $value, $owner, $group);

        /* conditions for settingProvider count of arguments */
        if (isset($group)) {
            static::assertEquals(null, $settings->get($name, $owner, $group));
        } elseif (isset($owner)) {
            static::assertEquals(null, $settings->get($name, $owner));
        } else {
            static::assertEquals(null, $settings->get($name));
        }
    }

    /**
     * @return array
     */
    public function setDataWithNullDefaultValueProvider(): array
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
            ->will(static::throwException(new \Exception));

        $settingsRepository
            ->method('findAll')
            ->will(static::throwException(new \Exception));

        $settings = new SettingsManager(
            $this->getRegistry($settingsRepository),
            ['parameter' => 'default']
        );

        $settings->setDefault('parameter_1', 'default');
        $settings->set('parameter', 'foo', $this->getUserId(), 'testingGroup'); // apcu

        static::assertEquals('default', $settings->get('parameter', 1, 'testingGroup'));
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
            ->will(static::returnValue($settingsEntity));

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

        static::assertEquals('default', $settings->get('height', $this->getUserId()));

        $settings->set('height', '67cm', $this->getUserId(), 'testingGroup'); // apcu

        static::assertEquals('67cm', $settings->get('height', null, 'testingGroup'));

        $settings->clear($this->getUserId(), 'testingGroup');

        static::assertEquals('67cm', $settings->get('height'));
    }
}
