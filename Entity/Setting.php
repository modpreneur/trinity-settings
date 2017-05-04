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
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_name_owner_id_group_name", columns={"name", "owner_id", "group_name"} )
 *     }
 * )
 * @ORM\Entity(repositoryClass="SettingRepository")
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

    /** @var  bool */
    private $isPrivate;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * Set value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = \serialize($value);
    }


    /**
     * Get value
     *
     * @return array | string | null
     */
    public function getValue()
    {
        return \unserialize($this->value);
    }


    /**
     * @return string | null
     */
    public function getOwnerId(): ?string
    {
        return $this->ownerId;
    }


    /**
     * @deprecated
     *
     * @param string $ownerId
     *
     * @return Setting
     */
    public function setOwnerId($ownerId): Setting
    {
        return $this->setOwner($ownerId);
    }


    /**
     * @param int|object $owner
     *
     * @return Setting
     */
    public function setOwner($owner): Setting
    {
        if (\is_object($owner) && \method_exists($owner, 'getId')) {
            $owner = $owner->getId();
        }

        $this->ownerId = $owner;
        return $this;
    }


    /**
     * @return string | null
     */
    public function getGroup(): ?string
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


    /**
     * @return boolean
     */
    public function isIsPrivate(): bool
    {
        return $this->isPrivate;
    }


    /**
     * @param boolean $isPrivate
     */
    public function setIsPrivate($isPrivate)
    {
        $this->isPrivate = $isPrivate;
    }
}
