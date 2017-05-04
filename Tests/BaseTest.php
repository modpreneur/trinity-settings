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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRegistry($settingsRepository): \PHPUnit_Framework_MockObject_MockObject
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
            ->will(static::returnValue($entityManager));

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
