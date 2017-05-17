<?php

namespace Trinity\Bundle\SettingsBundle\Tests;

/**
 * Class Owner
 * @package Trinity\Bundle\SettingsBundle\Tests
 */
class Owner
{
    protected $id;

    /**
     * Owner constructor.
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }


    /**
     * Return Owner id.
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
