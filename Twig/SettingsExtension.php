<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Twig;

use Trinity\Bundle\SettingsBundle\Manager\SettingsManager;


/**
 * Class SettingsExtension
 * @package Trinity\Bundle\SettingsBundle\Twig
 */
class SettingsExtension extends \Twig_Extension
{

    /** @var  SettingsManager */
    protected $settings;


    /**
     * SettingsExtension constructor.
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
     * @return mixed
     */
    public function getSettings($name, $owner = null){
        return $this->settings->get($name, $owner);
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