<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCreateCommand extends Command
{
    protected static $defaultName = 'user:create';
    protected static $defaultDescription = 'Add user into database';


    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $encoder;


    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $entityManager;
        $this->encoder = $passwordEncoder;

        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->addArgument('roles', InputArgument::IS_ARRAY, 'Roles')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $roles = $input->getArgument('roles');

        $user = new User();
        $user->setEmail($username);
        $user->setPassword(
            $this->encoder->encodePassword($user, $password)
        );

        $user->setRoles($roles);

        $this->em->persist($user);
        $this->em->flush();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
