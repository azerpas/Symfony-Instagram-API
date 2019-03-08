<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use InstagramAPI\Request\Hashtag;

class InstaSearchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('insta:search')
            ->setDescription('Seach intagram account')
            ->addArgument('tags',InputArgument::IS_ARRAY,'Hashtags list?')
            ->addOption('only', null, InputOption::VALUE_REQUIRED);
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {$only = $input->getOption('only');
       foreach( $input->getArgument('tags') as $tag)
        { echo $tag;
            $info = $this->getUsresByHashtags($tag);
        echo $info;
        }   
    }

   

    private function getUsresByHashtags($hashtag)
    {   echo "Get instagram account by hashtags\n";
         $hashtag  =  new Hashtag("rferfreg");
         $hashtag->getInfo($hashtag);
    }
}