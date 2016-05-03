<?php

namespace Trinity\Bundle\SettingsBundle\Tests\Functional;

/**
 * Class TestClass
 * @package Trinity\Bundle\SettingsBundle\Tests\Functional
 */
class TestClass
{
    protected $name;

    /**
     * TestClass constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }
}