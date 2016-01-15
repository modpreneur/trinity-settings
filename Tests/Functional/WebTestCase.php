<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application as App;
use Symfony\Component\Console\Input\StringInput;
use Trinity\Bundle\SettingsBundle\Tests\TestCase;


/**
 * Class WebTestCase
 * @package Trinity\Bundle\SearchBundle\Tests\Functional
 */
class WebTestCase extends TestCase
{
    /**
     * @var App
     */
    protected static $application;

    /**
     * @var bool
     */
    protected static $isInit = false;


    protected function init()
    {

        if (self::$isInit === false) {

            exec('php bin/console.php doctrine:database:drop --force');
            exec('php bin/console.php doctrine:schema:create');
            exec('php bin/console.php doctrine:schema:update --force');

            $kernel = $this->createClient()->getKernel();
            $container = $kernel->getContainer();
            $em = $container->get('doctrine.orm.default_entity_manager');

            $data = new DataSet();
            $data->load($em);
        }

        self::$isInit = true;
    }


    public function setUp()
    {
        parent::setUp();
        $this->init();
    }


    /**
     * @param $command
     * @return int
     * @throws \Exception
     */
    protected static function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);

        return self::getApplication()->run(new StringInput($command));
    }


    /**
     * @return App
     */
    protected static function getApplication()
    {
        if (null === self::$application) {
            $client = static::createClient();

            self::$application = new App($client->getKernel());
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }


    /**
     * @param string $serviceName
     * @return object
     */
    protected function get($serviceName)
    {
        $kernel = $this->createClient()->getKernel();
        $container = $kernel->getContainer();

        return $container->get($serviceName);
    }

}