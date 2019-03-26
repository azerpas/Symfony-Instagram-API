<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use InstagramAPI\Request\Hashtag;
use App\Service\DBRequest;

class botCommand extends Command
  

{    protected static $defaultName = 'insta:bot';
      
    protected function configure()
    {
        $this
            ->setName('insta:bot')
            ->setDescription('Seach intagram account') 
            ->addArgument('arg', InputArgument::REQUIRED, 'arg')
        
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   $slots= App\Service\DBRequest::getSlots() ;
        
        $time = new \DateTime();
        $time->format('H');
        if ($slots[$time]=="on"){

        }
       
        
    }
   

    
}