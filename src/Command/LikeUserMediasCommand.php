<?php

// src/Command/LikeUserMediasCommand.php
namespace App\Command;

use InstagramAPI\Response\Model\FriendshipStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;

class LikeUserMediasCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:likeUserMedias';

    protected function configure()
    {
        $this 
        ->setDescription('Like 2 medias of an Instagram user')
        ->addArgument('username', InputArgument::REQUIRED, 'My username')
        ->addArgument('password', InputArgument::REQUIRED, 'My password')
        ->addArgument('userId', InputArgument::REQUIRED, 'The Id of the account whose some medias we want to like')
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
        $toLikeId = $input->getArgument('userId');
        $mustEndWith = '_';
        $mustEndWith .= $toLikeId;
        $mediaIds = [];
        try {
            $ig->login($username, $password);
            $infos=$ig->timeline->getUserFeed($toLikeId);
            $tok = strtok($infos, ",");

            while ($tok !== false) {
                if ($this->startsWith($tok,'"id":')) {
                    $tok_temp = str_replace('"','',$tok);
                    $tok_temp = str_replace('id:','',$tok_temp);
                    if ($this->endsWith($tok_temp,$mustEndWith)) {
                        array_push($mediaIds,$tok_temp);
                    }
                }
                $tok = strtok(",");
            }
            //$output->writeln('Media Ids have been added in mediaIds');
            /*foreach ($mediaIds as $mediaId) {
                $output->writeln($mediaId);
            }*/
            //$output->write(sizeof($mediaIds));
            //$output->writeln(' mediaIds');
            $likesNumber=0;
            $likeCommand = $this->getApplication()->find('app:like');
            $randomNumbers = [];
            for($i=0;$i<2;) {
                $randomNumber=rand(1,sizeof($mediaIds));
                //$output->writeln($randomNumber);
                if (in_array($randomNumber,$randomNumbers)==false) {
                    array_push($randomNumbers,$randomNumber);
                    $i++;
                }
            }
            while($likesNumber<2) {
                $mediaId=$mediaIds[($randomNumbers[$likesNumber]-1)];
                $likeArgument = [
                    'command' => 'app:like',
                    'username' => $username,
                    'password' => $password,
                    'mediaId' => $mediaId,
                ];
                $likeInput = new ArrayInput($likeArgument);
                $likeCommand->run($likeInput, $output);
                sleep(5);
                $likesNumber++;
            }
            //$output->write($likesNumber);
            //$output->writeln(' medias have been liked');
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
  
    // Function to check the string is ends  
    // with given substring or not 
    private function endsWith($string, $endString) 
    { 
        $len = strlen($endString); 
        if ($len == 0) { 
            return true; 
        } 
        return (substr($string, -$len) === $endString); 
    }

}