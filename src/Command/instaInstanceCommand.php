<?php

namespace App\Command;

use InstagramAPI\Response\Model\FriendshipStatus;
use InstagramAPI\Response\Model\UnpredictableKeys\FriendshipStatusUnpredictableContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class instaInstanceCommand extends Command
{
   

    protected function configure()
    {
        $this
            ->setName('insta:instance')
            ->setDescription('get instagram instance')
            ->addArgument('username', InputArgument::REQUIRED, 'Instagram Username')
            ->addArgument('password', InputArgument::REQUIRED, 'Instagram Password')
            ->addOption('only', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io            = new SymfonyStyle($input, $output);
        $only = $input->getOption('only');
        try{
            return $this->getInstagramInstance($input->getArgument('username'), $input->getArgument('password'));
        }catch (\Exception $e){
            throw new \Exception('Getting Instagram Instance went wrong: ' . $e->getMessage());
        }

  
       
    }

    private function getInstagramInstance($username, $password)
    {
        /////// CONFIG ///////
        $debug          = false;
        $truncatedDebug = false;
        //////////////////////

        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
        try {
            $ig->login($username, $password);
            return $ig;
        } catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }
    }

}
