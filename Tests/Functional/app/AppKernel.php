<?php

namespace Trinity\Bundle\SettingsBundle\Tests\Functional\app;

// get the autoload file
$dir = __DIR__;
$lastDir = null;

while ($dir !== $lastDir) {
    $lastDir = $dir;
    if (file_exists($dir.'/autoload.php')) {
        $loader = require $dir.'/autoload.php';
        break;
    }
    if (file_exists($dir.'/autoload.php.dist')) {
        $loader = require $dir.'/autoload.php.dist';
        break;
    }
    if (file_exists($dir.'/vendor/autoload.php')) {
        $loader = require $dir.'/vendor/autoload.php';
        break;
    }
    $dir = dirname($dir);
}


\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;


/**
 * Class AppKernel.
 */
class AppKernel extends Kernel
{

    /**
     * @return array
     */
    public function registerBundles()
    {
        return array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Trinity\Bundle\SettingsBundle\SettingsBundle(),
        );
    }


    /**
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
    }


    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/';
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

}