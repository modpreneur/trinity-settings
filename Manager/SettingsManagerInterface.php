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
     * @param mixed $value string, int, boolean, object, ...
     * @param int|null $owner
     * @param null|string $group
     * @return $this
     */
    public function set($name, $value, $owner = null, $group = null);


    /**
     * @param int|null $owner int or entity with method 'getId()'
     * @param null|string $group
     * @return array
     */
    public function all($owner = null, $group = null);


    /**
     * @param string $name
     * @param int|null $owner
     * @param null|string $group
     * @return mixed
     */
    public function get($name, $owner = null, $group = null);


    /**
     * @param array $settings
     * @param int|null $owner
     * @param null|string $group
     * @return $this
     */
    public function setMany(array $settings, $owner = null, $group = null);


    /**
     * @param int $owner
     * @param null|string $group
     */
    public function clear($owner = null, $group = null);


    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setDefault($name, $value);


    /**
     * @param string $name
     * @param int|null $owner
     * @param null|string $group
     * @return bool
     */
    public function has($name, $owner = null, $group = null): bool;
}
