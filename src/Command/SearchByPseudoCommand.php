<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use InstagramAPI\Request\Hashtag;
use InstagramAPI\Signatures;
class SearchByPseudoCommand extends ContainerAwareCommand
{    protected static $defaultName = 'search:pseudo';
    protected function configure()
    {
        $this
            ->setName('search:pseudo')
            ->setDescription('Search intagram account') 
            ->addArgument('username', InputArgument::REQUIRED, 'My username')
            ->addArgument('password', InputArgument::REQUIRED, 'My password')
            ->addArgument('pseudo',InputArgument::IS_ARRAY,'users list?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {    /////// CONFIG ///////
        $debug          = false;
        $truncatedDebug = false;
        //////////////////////
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $users= $input->getArgument('pseudo');
        
       
        //get instagram instance
        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug); 
        try {
            $ig->login($username, $password);
          
        } catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }
        foreach ($users as $user){
            $followersList= $ig->people->getFollowers($user, \InstagramAPI\Signatures::generateUUID());  
            
            foreach ($followersList->getUsers() as $follower) {
               
                 array_push(array("id"=>$follower->getUserId(),"usename"=> $follower->getUsername())) ;
               }
             
        } 
        
        
        
        
       
         
    }

    private function saveUser ($userId){
        
    }

   

    
}