<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Manager;

/**
 * Interface SettingsManagerInterface
 * @package Trinity\Bundle\SettingsBundle\Manager
 */
interface SettingsManagerInterface
{

    /**
     * @param string $name
     * @param mixed $value  string, int, boolean, object, ...
     * @param int|entity|null $owner int or entity with method 'getId()'
     * @return $this
     */
    function set($name, $value, $owner = null);


    /**
     * @param int|entity|null $owner int or entity with method 'getId()'
     * @return array
     */
    function all($owner = null);


    /**
     * @param string $name
     * @param int|entity|null $owner int or entity with method 'getId()'
     * @return mixed
     */
    function get($name, $owner = null);


    /**
     * @param array $settings
     * @param int|entity|null $owner int or entity with method 'getId()'
     * @return $this
     */
    function setMany(array $settings, $owner = null);


    /**
     * @param int|entity|null $owner int or entity with method 'getId()
     * @return void
     */
    function clear($owner = null);


    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    function setDefault($name, $value);

}