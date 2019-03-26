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
        //Add respectively the username and password of the user and the userId whose pictures/videos we want to like
        ->addArgument('username', InputArgument::REQUIRED, 'My username')
        ->addArgument('password', InputArgument::REQUIRED, 'My password')
        ->addArgument('userId', InputArgument::REQUIRED, 'The Id of the account whose pictures/videos we want to like')
    ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
        $command = $this->getApplication()->find('insta:instance');

        $arguments = [
            'command'  => 'insta:instance',
            'username' => $input->getArgument('username') ,
            'password' => $input->getArgument('password') ,
        ];

        $instanceInput = new ArrayInput($arguments);
        $ig = $command->run($instanceInput, $output);
        */

        /////// CONFIG ///////
        $debug          = false;
        $truncatedDebug = false;
        //////////////////////
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

        $toLikeId = $input->getArgument('userId');
        $mediaIds = [];
        try {
            $ig->login($username, $password);
            
            $infos=$ig->timeline->getUserFeed($toLikeId);
            $tok = strtok($infos, ",");
            
            while ($tok !== false) {
                //$output->writeln($tok);
                if ($this->startsWith($tok,'"id":')) {

                    //$tok_temp = str_replace('id:','',$tok);
                    $tok_temp = str_replace('"','',$tok);
                    $tok_temp = str_replace('id:','',$tok_temp);
                    //$output->writeln($tok_temp);
                    array_push($mediaIds,$tok_temp);
                    //$ig->media->like($tok_temp);
                }
                //echo "Word=$tok<br />";
                $tok = strtok(",");
            }
            $output->writeln('Media Ids have been added in mediaIds');
            //var_dump($mediaIds);
            foreach ($mediaIds as $mediaId) {
                $output->writeln($mediaId);
            }
            $output->writeln(sizeof($mediaIds));
            $nombreDeLikes=0;
           
            while($nombreDeLikes<4) {
                echo 'test';
                $mediaId=$mediaIds[$nombreDeLikes];
                $ig->media->like($mediaId);
               // sleep(30);
                $nombreDeLikes++;
            }
            $output->write($nombreDeLikes);
            $output->writeln(' have been liked');
        } catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }        
    }

    // Function to check string starting 
    // with given substring 
    private function startsWith ($string, $startString) 
    { 
        $len = strlen($startString); 
        return (substr($string, 0, $len) === $startString); 
    } 
  

}