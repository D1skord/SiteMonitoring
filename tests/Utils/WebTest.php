<?php

namespace App\Tests\Utils;

use Doctrine\ORM\EntityManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

abstract class WebTest extends WebTestCase
{
    /**
     * Веб-клиент
     *
     * @var KernelBrowser
     */
    public $client;

    /**
     * Менеджер сущностей.
     *
     * @var EntityManager
     */
    protected $entityManager;

    protected AbstractDatabaseTool $databaseTool;

    /**
     * Установка окружения.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->databaseTool = static::$kernel->getContainer()->get(DatabaseToolCollection::class)->get();
    }

    /**
     * Добавляет фикстуру.
     *
     * @param object $fixture
     *
     * @throws \Doctrine\ORM\ORMException
     */
    protected function addFixture($fixture): void
    {
        $this->entityManager->persist($fixture);
        $this->entityManager->flush();
    }

    /**
     * Если критерии не указаны, возвращается сущность с последним id записи в БД.
     *
     * @template T of object
     * @param class-string<T> $class
     * @return T|null
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    protected function getEntity(string $class, array $criteria = [], array $orderBy = ['id' => 'DESC']): ?object
    {
        $entityList = $this->entityManager->getRepository($class)->findBy($criteria, $orderBy);

        if (empty($entityList)) {
            return null;
        }

        return $entityList[0];
    }
}
