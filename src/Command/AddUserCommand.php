<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\PasswordHasher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddUserCommand extends Command
{
    private $user_repository;
    private $entity_manager;
    private $hasher;

    public function __construct(
        UserRepository         $user_repository,
        EntityManagerInterface $entity_manager,
        PasswordHasher         $hasher
    ) {
        parent::__construct();
        $this->user_repository = $user_repository;
        $this->entity_manager  = $entity_manager;
        $this->hasher          = $hasher;
    }
    
    public function configure()
    {
        $this->setName('app:create-user')
            ->setDescription('Create a new user.')
            ->setHelp('USAGE: app:create-user USERNAME');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $username = readline('Please provide a username: ');
        $password = exec(
            '/usr/bin/env bash -c "'
            . "read -rsp 'Please provide a password: '"
            . " PASSWORD </dev/tty && echo ". '\"\$PASSWORD\""'
        );

        $user = new User($username, $this->hasher->hash($password));
        $this->entity_manager->persist($user);
        $this->entity_manager->flush();
    }
}
