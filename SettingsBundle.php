<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Trinity\Bundle\SettingsBundle\DependencyInjection\TrinitySettingsExtension;


/**
 * Class SettingsBundle
 * @package Trinity\Bundle\SettingsBundle
 */
class SettingsBundle extends Bundle
{

    public function build(ContainerBuilder $container){
        parent::build($container);
        $container->registerExtension(new TrinitySettingsExtension() );
    }
}
