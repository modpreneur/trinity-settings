<?php

namespace Tests;

use Doctrine\Bundle\DoctrineBundle\Registry;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class BaseTest
 * @package Tests
 */
abstract class BaseTest extends TestCase
{

    /**
     * @return Registry
     */
    protected function getRegistry($settingsRepository)
    {
        $registry = $this
            ->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager->method('getRepository')
            ->willReturn($settingsRepository);

        $registry->method('getManager')
            ->will($this->returnValue($entityManager));

        return $registry;
    }


    /**
     * Return user id. $this->getUser()->getId();
     * @return int
     */
    protected function getUserId(): int
    {
        return 1;
    }
}
