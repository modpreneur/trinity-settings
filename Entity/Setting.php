<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Setting
 *
 * @ORM\Table(
 *  name="trinity_settings",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="unique_name", columns={"name"}, options={"where": "(owner_id IS NULL) AND (group_name IS NULL)"} ), @ORM\UniqueConstraint(name="unique_name_owner_id", columns={"name", "owner_id"} ), @ORM\UniqueConstraint(name="unique_name_group", columns={"name", "group_name"} )}
 * )
 * @ORM\Entity
 */
class Setting
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="owner_id", type="string", length=255, nullable=true)
     */
    private $ownerId;

    /**
     * @var string
     *
     * @ORM\Column(name="group_name", length=64, nullable=true)
     */
    private $group;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Setting
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Setting
     */
    public function setValue($value)
    {
        $this->value = serialize($value);

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return unserialize($this->value);
    }

    /**
     * @return string
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param string $ownerId
     *
     * @return Setting
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;

        return $this;
    }


    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }


    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

}
