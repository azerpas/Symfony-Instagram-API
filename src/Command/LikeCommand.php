<?php

// src/Command/LikeCommand.php
namespace App\Command;

use InstagramAPI\Response\Model\FriendshipStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;

class LikeCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:like';

    protected function configure()
    {
        $this 
        ->setDescription('Like an Instagram media')
        ->addArgument('username', InputArgument::REQUIRED, 'My username')
        ->addArgument('password', InputArgument::REQUIRED, 'My password')
        ->addArgument('mediaId', InputArgument::REQUIRED, 'The Id of the media to like')
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
        $toLikeId = $input->getArgument('mediaId');
        try {
            $ig->login($username, $password);
            $ig->media->like($toLikeId);
            $output->writeln('Like done!');
        } catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }
    }
}