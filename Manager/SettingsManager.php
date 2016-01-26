<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Manager;

use Doctrine\ORM\EntityManager;
use Trinity\Bundle\SettingsBundle\Entity\Setting;


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

    /** @var array  */
    protected $defaults = [];

    /**
     * SettingsManager constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    /**
     * @param string $name
     * @param mixed $value string, int, boolean, object, ...
     * @param int|entity|null $owner int or entity with method 'getId()'
     * @return $this
     */
    function set($name, $value, $owner = null)
    {
        $item = $this->get($name, $owner);
        $nname = ($owner != null )? $name . '_' . $owner:$name;

        if( !(array_key_exists($name, $this->defaults) && $this->defaults[$name] == $item) || $item != null){
            $setting = $this->em->getRepository('SettingsBundle:Setting')->findOneBy(['name' => $nname, 'ownerId' => $owner]);
            if($setting == null) $setting = new Setting();
        }else{
            $setting = new Setting();
        }

        $setting->setName($nname);
        $setting->setValue($value);
        $setting->setOwnerId($owner);

        $this->em->persist($setting);
        $this->em->flush($setting);

        return $this;
    }


    /**
     * @param int|entity|null $owner int or entity with method 'getId()'
     * @return array
     */
    function all($owner = null)
    {
        $all = $this->findAllByOwner($owner);
        $rows = [];

        foreach($all as $row){
            $rows[$row->getName()] = $this->get($row->getName());
        }

        return $rows;
    }


    /**
     * @param string $name
     * @param int|entity|null $owner int or entity with method 'getId()'
     * @return mixed
     */
    function get($name, $owner = null)
    {
        if($owner) $name .= '_' . $owner;

        $property = $this->getOneByOwner($name, $owner);

        if(null == $property && array_key_exists($name, $this->defaults)){
            $property = unserialize($this->defaults[$name]);
        }elseif($property instanceof \Trinity\Bundle\SettingsBundle\Entity\Setting){
            $property = $property->getValue();
        }

        return $property;
    }


    /**
     * @param array $settings
     * @param int|entity|null $owner int or entity with method 'getId()'
     * @return $this
     */
    function setMany(array $settings, $owner = null)
    {
        foreach($settings as $name => $value){
            $this->set($name, $value, $owner);
        }

        return $this;
    }


    /**
     * @param int|entity|null $owner int or entity with method 'getId()
     * @return void
     */
    function clear($owner = null)
    {
        $rows = $this->findAllByOwner($owner);

        foreach($rows as $row){
            $this->em->remove($row);
        }

        $this->em->flush();
    }


    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    function setDefault($name, $value)
    {
        $this->defaults[$name] = serialize($value);
    }


    /**
     * @param $name
     * @param $owner
     * @return null|object|Setting
     */
    protected function getOneByOwner($name, $owner){
        if($owner){
            $property = $this->em->getRepository('SettingsBundle:Setting')->findOneBy(["name" => $name, "ownerId" => $owner]);
        }else{
            $property = $this->em->getRepository('SettingsBundle:Setting')->findOneBy(["name" => $name]);
        }

        return $property;
    }


    /**
     * @param $owner
     * @return array|\Trinity\Bundle\SettingsBundle\Entity\Setting[]
     */
    protected function findAllByOwner($owner){
        if($owner){
            $properties = $this->em->getRepository('SettingsBundle:Setting')->findBy(["ownerId" => $owner]);
        }else{
            $properties = $this->em->getRepository('SettingsBundle:Setting')->findAll();
        }

        return $properties;
    }

}