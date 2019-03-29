<?php

// src/Command/FollowCommand.php
namespace App\Command;

use InstagramAPI\Response\Model\FriendshipStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;

class FollowCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:follow';

    protected function configure()
    {
        $this 
        ->setDescription('Follow an Instagram user')
        ->addArgument('username', InputArgument::REQUIRED, 'My username')
        ->addArgument('password', InputArgument::REQUIRED, 'My password')
        ->addArgument('userId', InputArgument::REQUIRED, 'The Id of the account to follow')
    ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /////// CONFIG ///////
        $debug          = false;
        $truncatedDebug = false;
        //////////////////////
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
        $toFollowId = $input->getArgument('userId');
        try {
            $ig->login($username, $password);
            $ig->people->follow($toFollowId);
            //$output->writeln('Follow done!');
        } catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }
    }

}