<?php

// src/Command/LikeAndFollowUsersCommand.php
namespace App\Command;

use InstagramAPI\Response\Model\FriendshipStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;

class LikeAndFollowUsersCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:likeAndFollowUsers';

    protected function configure()
    {
        $this 
        ->setDescription('Like Medias and follow Instagram users from the People table for an account')
        ->addArgument('username', InputArgument::REQUIRED, 'My username')
        ->addArgument('password', InputArgument::REQUIRED, 'My password')
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
        try {
            $ig->login($username, $password);
            //DBRequest to get peopleToInteract
            $peopleToInteract = [];
            array_push($peopleToInteract,2114362733);
            array_push($peopleToInteract,7171634018);
            array_push($peopleToInteract,3869473816);
            $likeUserMediasCommand = $this->getApplication()->find('app:likeUserMedias');
            $followCommand = $this->getApplication()->find('app:follow'); 
            //LikeUserMedias and follow people
            foreach($peopleToInteract as $person) {
                //Like 2 pictures of person
                $likeUserMediasArguments = [
                    'command' => 'app:likeUserMedias',
                    'username' => $username,
                    'password' => $password,
                    //'userId' => $person->getId(),
                    'userId' => $person,
                ];
                $likeUserMediasInput = new ArrayInput($likeUserMediasArguments);
                $likeUserMediasCommand->run($likeUserMediasInput, $output);
                //Follow person
                $followCommandArguments = [
                    'command' => 'app:follow',
                    'username' => $username,
                    'password' => $password,
                    //'userId' => $person->getId(),
                    'userId' => $person,
                ];
                $followInput = new ArrayInput($followCommandArguments);
                sleep();
                $followCommand->run($followInput, $output); 
                //Set toFollow to 'true' and toFollowDate to Date  
            }
        }
        catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }        
    }    
}