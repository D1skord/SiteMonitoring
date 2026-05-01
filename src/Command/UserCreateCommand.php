<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:user:create')]
class UserCreateCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Plain password. If omitted, the command asks for it interactively.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = trim((string) $input->getArgument('email'));
        $password = $input->getOption('password');

        if ($email === '') {
            $output->writeln('<error>Email cannot be empty.</error>');

            return Command::FAILURE;
        }

        if (!is_string($password) || $password === '') {
            $password = $this->askPassword($input, $output);
        }

        if ($password === '') {
            $output->writeln('<error>Password cannot be empty.</error>');

            return Command::FAILURE;
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);
        $isNewUser = !$user instanceof User;

        if ($isNewUser) {
            $user = new User();
            $user->setEmail($email);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->userRepository->save($user, true);

        $output->writeln(sprintf(
            '<info>User "%s" %s.</info>',
            $email,
            $isNewUser ? 'created' : 'password updated'
        ));

        return Command::SUCCESS;
    }

    private function askPassword(InputInterface $input, OutputInterface $output): string
    {
        $question = new Question('Password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        return (string) $helper->ask($input, $output, $question);
    }
}
