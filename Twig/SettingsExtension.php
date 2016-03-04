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
            new \Twig_SimpleFunction('get_setting', [$this, 'getSetting']),
            new \Twig_SimpleFunction('has_setting', [$this, 'hasSetting']),
        ];
    }


    /**
     * @param string $name
     * @param int|null $owner
     * @param null|string $group
     * @return mixed|null
     */
    public function getSetting($name, $owner = null, $group = null){

        $value = null;

        try{
            $value =  $this->settings->get($name, $owner, $group);
        }catch( PropertyNotExistsException $ex ){
            $value =  null;
        }

        return $value;
    }


    /**
     * @param string $name
     * @param int|null $owner
     * @param null|string $group
     * @return mixed|null
     */
    public function hasSetting($name, $owner = null, $group = null){
        return $this->settings->has($name, $owner, $group);;
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