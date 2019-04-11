<?php

// src/Command/LikeAndFollowUsersCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\DBRequest;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Process\Process;



class TestCommand extends Command
{
    protected static $defaultName = 'test:main';

    /**
     * @var DBRequest
     */
    private $db;

    private $entityManager;

    public function __construct(DBrequest $dbRequest,EntityManagerInterface $entityManager){
        $this->db = $dbRequest;
        $this->entityManager=$entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Testing main command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        for ($i = 0; $i<10; $i++) {
            $output->writeln('Writing...');
            $output->writeln('Sleeping...');
            sleep(3);
        }


    }
}