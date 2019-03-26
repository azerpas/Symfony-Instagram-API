<?php

namespace App\Command;
set_time_limit(0);
date_default_timezone_set('UTC');


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use InstagramAPI\Request\Hashtag;
use InstagramAPI\Signatures;
class SearchByTagCommand extends ContainerAwareCommand
{    protected static $defaultName = 'search:tag';
    protected function configure()
    {
        $this
            ->setName('search:tag')
            ->setDescription('Seach intagram account') 
            ->addArgument('username', InputArgument::REQUIRED, 'My username')
            ->addArgument('password', InputArgument::REQUIRED, 'My password')
            ->addArgument('tags',InputArgument::IS_ARRAY,'Hashtags list?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {    /////// CONFIG ///////
        $debug          = false;
        $truncatedDebug = false;
        //////////////////////
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $tags= $input->getArgument('tags');
        
       
        //get instagram instance
        $ig = new \InstagramAPI\Instagram($debug,$truncatedDebug); 
        try {
            $ig->login($username,$password);
          
        } catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }
        foreach ($tags as $tag){
            $maxId=null;
            $users=[];
            $rankToken = Signatures::generateUUID();
            do {
             
            $feed = $ig->hashtag->getFeed($tag, $rankToken, $maxId);
              //dump($feed);
            foreach ($feed->getItems() as $item ) {
              
               array_push(array("id"=>$item->getUser()->getUserId(),"usename"=> $item->getUser()->getUsername())) ;
               }
              $maxId=$feed->getNextMaxId();
              sleep(5);
              
            }while ( $maxId !== null);
        } 
     
    }

   

    
}