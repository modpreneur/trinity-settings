<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Twig;

use Trinity\Bundle\SettingsBundle\Exception\PropertyNotExistsException;
use Trinity\Bundle\SettingsBundle\Manager\SettingsManager;


/**
 * Class TrinitySettingsExtension
 * @package Trinity\Bundle\SettingsBundle\Twig
 */
class SettingsExtension extends \Twig_Extension
{

    /** @var  SettingsManager */
    protected $settings;


    /**
     * TrinitySettingsExtension constructor.
     * @param SettingsManager $settings
     */
    public function __construct(SettingsManager $settings)
    {
        $this->settings = $settings;
    }


    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('get_settings', [$this, 'getSettings'])
        ];
    }


    /**
     * @param string $name
     * @param int|null $owner
     * @param null|string $group
     * @param string|int|bool|null $default
     * @return mixed|null
     */
    public function getSettings($name, $owner = null, $group = null, $default = null){

        $value = null;

        try{
            $value =  $this->settings->get($name, $owner, $group);
        }catch( PropertyNotExistsException $ex ){
            $value =  null;
        }

        if(null == $value && null != $default){
            $value = $default;
        }

        return $value;
    }


    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'settings_extension';
    }
}