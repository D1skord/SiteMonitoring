<?php

namespace App\Tests\Functional\Command;

use App\Command\UserCreateCommand;
use App\Entity\User;
use App\Tests\Utils\WebTest;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCreateCommandTest extends WebTest
{
    public function testCreateUser(): void
    {
        $commandTester = $this->createCommandTester();
        $commandTester->execute([
            'email' => 'admin@example.com',
            '--password' => 'first-password',
        ]);

        $commandTester->assertCommandIsSuccessful();

        $user = $this->getEntity(User::class, ['email' => 'admin@example.com']);

        self::assertInstanceOf(User::class, $user);
        self::assertTrue($this->getPasswordHasher()->isPasswordValid($user, 'first-password'));
    }

    public function testUpdateExistingUserPassword(): void
    {
        $commandTester = $this->createCommandTester();
        $commandTester->execute([
            'email' => 'admin@example.com',
            '--password' => 'first-password',
        ]);

        $commandTester->execute([
            'email' => 'admin@example.com',
            '--password' => 'second-password',
        ]);

        $commandTester->assertCommandIsSuccessful();

        $users = $this->entityManager->getRepository(User::class)->findBy(['email' => 'admin@example.com']);

        self::assertCount(1, $users);
        self::assertTrue($this->getPasswordHasher()->isPasswordValid($users[0], 'second-password'));
        self::assertFalse($this->getPasswordHasher()->isPasswordValid($users[0], 'first-password'));
    }

    private function createCommandTester(): CommandTester
    {
        $application = new Application(static::$kernel);
        $command = $application->find('app:user:create');

        return new CommandTester($command);
    }

    private function getPasswordHasher(): UserPasswordHasherInterface
    {
        return static::getContainer()->get(UserPasswordHasherInterface::class);
    }
}
