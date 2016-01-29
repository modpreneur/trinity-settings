<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Manager;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManager;
use Trinity\Bundle\SettingsBundle\Entity\Setting;
use Trinity\Bundle\SettingsBundle\Exception\PropertyNotExistsException;


/**
 * Class SettingsManager
 * @package Trinity\Bundle\SettingsBundle\Manager
 */
class SettingsManager implements SettingsManagerInterface
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var CacheProvider
     */
    protected $cacheProvider;

    /** @var array */
    protected $defaults = [];


    /**
     * SettingsManager constructor.
     * @param EntityManager $em
     * @param array $settings
     * @param CacheProvider $cacheProvider
     */
    public function __construct(EntityManager $em, array $settings, CacheProvider $cacheProvider = null)
    {
        $this->em = $em;
        $this->cacheProvider = $cacheProvider;

        if($settings){
            foreach($settings as $name => $value){
                if(!$this->has($name))
                    $this->setDefault($name, $value);
            }
        }
    }


    /**
     * @param CacheProvider $cacheProvider
     */
    public function setCacheProvider($cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }


    /**
     * @return CacheProvider
     */
    public function getCacheProvider()
    {
        return $this->cacheProvider;
    }


    /**
     * @param string $name
     * @param mixed $value string, int, boolean, object, ...
     * @param int|entity|null $owner int or entity with method 'getId()'
     * @param null|string $group
     * @return $this
     */
    function set($name, $value, $owner = null, $group = null)
    {
        try {
            $item = $this->get($name, $owner, $group);
        } catch (PropertyNotExistsException $ex) {
            $item = null;
        }

        $nname = ($owner != null) ? $name.'_'.$owner : $name;

        if (!(array_key_exists($name, $this->defaults) && $this->defaults[$name] == $item) || $item != null) {
            $setting = $this->em->getRepository('SettingsBundle:Setting')->findOneBy(
                ['name' => $nname, 'ownerId' => $owner, 'group' => $group]
            );
            if ($setting == null) {
                $setting = new Setting();
            }
        } else {
            $setting = new Setting();
        }

        $setting->setName($nname);
        $setting->setValue($value);
        $setting->setOwnerId($owner);
        $setting->setGroup($group);

        $this->em->persist($setting);
        $this->em->flush($setting);

        if ($this->cacheProvider) {
            $this->cacheProvider->save($nname, serialize($setting));
        }

        return $this;
    }


    /**
     * @param int|null $owner
     * @param null $group
     * @return array
     * @throws PropertyNotExistsException
     */
    function all($owner = null, $group = null)
    {
        $all = $this->findAllByOwner($owner, $group);
        $rows = [];

        foreach ($all as $row) {
            $rows[$row->getName()] = $this->get($row->getName(), $owner, $group);
        }

        return $rows;
    }


    /**
     * @param string $name
     * @param int|null $owner
     * @param null|string $group
     * @return mixed
     * @throws PropertyNotExistsException
     */
    function get($name, $owner = null, $group = null)
    {
        if ($owner) { $name .= '_'.$owner; }

        $property = $this->getOneByOwner($name, $owner, $group);

        if (null == $property && array_key_exists($name, $this->defaults)) {
            $property = unserialize($this->defaults[$name]);
        } elseif ($property instanceof \Trinity\Bundle\SettingsBundle\Entity\Setting) {
            $property = $property->getValue();
        } else {
            throw new PropertyNotExistsException('Property \''.$name.'\' doesn\'t exists.');
        }

        return $property;
    }


    /**
     * @param array $settings
     * @param int|null $owner
     * @param null|string $group
     * @return $this
     */
    function setMany(array $settings, $owner = null, $group = null)
    {
        foreach ($settings as $name => $value) {
            $this->set($name, $value, $owner, $group);
        }

        return $this;
    }


    /**
     * @param int|null $owner
     * @param null $group
     */
    function clear($owner = null, $group = null)
    {
        $rows = $this->findAllByOwner($owner, $group);

        foreach ($rows as $row) {
            $this->em->remove($row);
        }

        $this->em->flush();

        if ($this->cacheProvider) {
            $this->cacheProvider->deleteAll();
        }
    }


    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    function setDefault($name, $value)
    {
        $this->defaults[$name] = serialize($value);
        $this->set($name, $value); // try..
    }


    /**
     * @param string $name
     * @param int $owner
     * @param null|string $group
     * @return null|object|Setting
     */
    protected function getOneByOwner($name, $owner, $group = null)
    {
        $property = null;

        if ($this->cacheProvider) {
            $property = unserialize($this->cacheProvider->fetch($name));
        }

        if (null == $property) {
            if ($owner) {
                $property = $this->em->getRepository('SettingsBundle:Setting')->findOneBy( ["name" => $name, "ownerId" => $owner] );
            }elseif($group){
                $property = $this->em->getRepository('SettingsBundle:Setting')->findOneBy(["name" => $name, 'group' => $group]);
            }elseif($group && $owner){
                $property = $this->em->getRepository('SettingsBundle:Setting')->findOneBy(["name" => $name, 'group' => $group, 'ownerId' => $owner]);
            }else {
                $property = $this->em->getRepository('SettingsBundle:Setting')->findOneBy(["name" => $name]);
            }
        }

        return $property;
    }


    /**
     * @param int|null $owner
     * @param null|string $group
     * @return array|\Trinity\Bundle\SettingsBundle\Entity\Setting[]
     */
    protected function findAllByOwner($owner = null, $group = null)
    {
        if ($owner) {
            $properties = $this->em->getRepository('SettingsBundle:Setting')->findBy(["ownerId" => $owner]);
        }elseif($group){
            $properties = $this->em->getRepository('SettingsBundle:Setting')->findBy(["group" => $group]);
        }elseif($owner && $group){
            $properties = $this->em->getRepository('SettingsBundle:Setting')->findBy(["group" => $group, 'ownerId' => $owner]);
        }else {
            $properties = $this->em->getRepository('SettingsBundle:Setting')->findAll();
        }

        return $properties;
    }


    /**
     * @param string $name
     * @param int|null $owner
     * @param null|string $group
     * @return bool
     */
    function has($name, $owner = null, $group = null): bool
    {
        try {
            $this->get($name, $owner, $group);

            return true;
        } catch (PropertyNotExistsException $ex) {
            return false;
        }
    }
}