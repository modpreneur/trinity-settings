<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Manager;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Trinity\Bundle\SettingsBundle\Entity\Setting;
use Trinity\Bundle\SettingsBundle\Exception\PropertyNotExistsException;

/**
 * Class SettingsManager
 *
 * @package Trinity\Bundle\SettingsBundle\Manager
 */
class SettingsManager implements SettingsManagerInterface
{
    /** @var  RegistryInterface */
    protected $doctrineRegistry;

    /** @var  ObjectManager */
    protected $entityManager;

    /** @var CacheProvider*/
    protected $cacheProvider;

    /** @var array */
    protected $defaults = [];

    /** @var  [] */
    protected $settings;

    /**
     * SettingsManager constructor.
     * @param RegistryInterface $registry
     * @param array $settings
     * @param CacheProvider $cacheProvider
     */
    public function __construct(RegistryInterface $registry, array $settings, CacheProvider $cacheProvider = null)
    {
        $this->doctrineRegistry = $registry;
        $this->cacheProvider = $cacheProvider;
        $this->settings = $settings;
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
     * @param int $owner ($user->getId())
     * @param null|string $group
     * @return $this
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function set($name, $value, $owner = null, $group = null)
    {
        try {
            $item = $this->get($name, $owner, $group);
        } catch (PropertyNotExistsException $ex) {
            $item = null;
        }

        $nname = (null !== $owner) ? $name . '_' . $owner : $name;

        if (null !== $item || !(array_key_exists($name, $this->defaults) && $this->defaults[$name] === $item)) {
            $setting = $this->getEntityManager()->getRepository('SettingsBundle:Setting')->findOneBy(
                ['name' => $nname, 'ownerId' => $owner, 'group' => $group]
            );
            if (null === $setting) {
                $setting = new Setting();
            }
        } else {
            $setting = new Setting();
        }

        $setting->setName($nname);
        $setting->setValue($value);
        $setting->setOwnerId($owner);
        $setting->setGroup($group);

        $this->getEntityManager()->persist($setting);
        $this->getEntityManager()->flush();

        if ($this->cacheProvider) {
            $this->cacheProvider->save($nname, serialize($setting));
        }

        return $this;
    }


    /**
     * @param int|null $owner $owner ($user->getId())
     * @param null $group
     * @return array
     * @throws PropertyNotExistsException
     */
    public function all($owner = null, $group = null)
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
     * @param int|null $owner $owner ($user->getId())
     * @param null|string $group
     * @return mixed
     * @throws PropertyNotExistsException
     */
    public function get($name, $owner = null, $group = null)
    {
        $nName = $name;

        if ($owner) {
            $name .= '_' . $owner;
        }

        $property = $this->getOneByOwner($name, $owner, $group);

        if (null === $property && array_key_exists($nName, $this->defaults)) {
            $property = unserialize($this->defaults[$name]);
        } elseif ($property instanceof Setting) {
            $property = $property->getValue();
        } else {
            if (array_key_exists($nName, $this->settings)) {
                return $this->settings[$nName];
            }

            if (array_key_exists($group . '.' . $nName, $this->settings)) {
                return $this->settings[$group . '.' . $nName];
            }

            $message = 'Property \'' . $nName . '\' doesn\'t exists. ';

            if ($owner) {
                $message .= 'Owner ID is: \'' . $owner . '\'. ';
            }

            if ($group) {
                $message .= 'Group name is: \'' . $group . '\'. ';
            }

            $hint = implode(', ', $this->getSuggestion($nName));
            $defaults = array_keys($this->settings);
            $set = array_keys($this->all());
            $items = implode(', ', array_merge($defaults, $set));

            throw new PropertyNotExistsException(
                $message . 'Did you mean ' . $hint . '. Available properties are ' . $items . '.'
            );
        }

        return $property;
    }


    /**
     * @param array $settings
     * @param int|null $owner $owner ($user->getId())
     * @param null|string $group
     * @return $this
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function setMany(array $settings, $owner = null, $group = null)
    {
        foreach ($settings as $name => $value) {
            $this->set($name, $value, $owner, $group);
        }

        return $this;
    }


    /**
     * @param int|null $owner
     * @param null $group
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function clear($owner = null, $group = null)
    {
        $rows = $this->findAllByOwner($owner, $group);

        foreach ($rows as $row) {
            $this->getEntityManager()->remove($row);
        }

        $this->getEntityManager()->flush();

        $this->clearCacheProvider();
    }


    /**
     * Delete all in cache provider
     */
    public function clearCacheProvider()
    {
        if ($this->cacheProvider) {
            $this->cacheProvider->deleteAll();
        }
    }



    /**
     * @param string $name
     * @param string $value
     * @return $this
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function setDefault($name, $value)
    {
        $this->defaults[$name] = serialize($value);
        $this->set($name, $value); // try..

        return $this;
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

        if (null === $property || false === $property) {
            try {
                if ($group && $owner) {
                    $property = $this->getEntityManager()->getRepository('SettingsBundle:Setting')
                        ->findOneBy(['name' => $name, 'group' => $group, 'ownerId' => $owner]);
                } elseif ($owner) {
                    $property = $this->getEntityManager()->getRepository('SettingsBundle:Setting')
                        ->findOneBy(['name' => $name, 'ownerId' => $owner]);
                } elseif ($group) {
                    $property = $this->getEntityManager()->getRepository('SettingsBundle:Setting')
                        ->findOneBy(['name' => $name, 'group' => $group]);
                } else {
                    $property = $this->getEntityManager()->getRepository('SettingsBundle:Setting')
                        ->findOneBy(['name' => $name]);
                }
            } catch (\Exception $ex) {
                // -- build error  - select - no table
                return null;
            }
        }

        return $property;
    }


    /**
     * @param int|null $owner
     * @param null|string $group
     * @return array|\Trinity\Bundle\SettingsBundle\Entity\Setting[]
     * @throws \UnexpectedValueException
     */
    protected function findAllByOwner($owner = null, $group = null)
    {
        $entityManager = $this->getEntityManager();
        if ($owner) {
            $properties = $entityManager->getRepository('SettingsBundle:Setting')->findBy(['ownerId' => $owner]);
        } elseif ($group) {
            $properties = $entityManager->getRepository('SettingsBundle:Setting')->findBy(['group' => $group]);
        } elseif ($owner && $group) {
            $properties = $entityManager->getRepository('SettingsBundle:Setting')
                ->findBy(['group' => $group, 'ownerId' => $owner]);
        } else {
            $properties = $entityManager->getRepository('SettingsBundle:Setting')->findAll();
        }

        return $properties;
    }


    /**
     * @param string $name
     * @param int|null $owner
     * @param null|string $group
     * @return bool
     */
    public function has($name, $owner = null, $group = null): bool
    {
        try {
            $this->get($name, $owner, $group);

            return true;
        } catch (PropertyNotExistsException $ex) {
            return false;
        }
    }


    /**
     * @param string $value
     * @return array
     * @throws \Trinity\Bundle\SettingsBundle\Exception\PropertyNotExistsException
     */
    public function getSuggestion($value)
    {
        $defaults = array_keys($this->settings);
        $set = array_keys($this->all());
        /** @var array $items */
        $items = array_merge($defaults, $set);

        $norm = preg_replace($re = '#^(?=[A-Z])#', '', $value);
        $best = [];
        $min = (strlen($value) / 4 + 1) * 10 + .1;
        foreach ($items as $item) {
            if ($item !== $value
                && (
                    ($len = levenshtein($item, $value, 10, 11, 10)) < $min
                    || ($len = levenshtein(preg_replace($re, '', $item), $norm, 10, 11, 10) + 20) < $min
                )
            ) {
                $min = $len;
                $best[] = $item;
            }
        }
        return $best;
    }

    /**
     * @return ObjectManager
     */
    protected function getEntityManager()
    {
        if ($this->entityManager !== null) {
            return $this->entityManager;
        }

        return $this->doctrineRegistry->getManager();
    }

}
