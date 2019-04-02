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
use App\Service\DBRequest;
use Psr\Log\LoggerInterface;
class SearchByTagCommand extends ContainerAwareCommand
{    protected static $defaultName = 'search:tag';
     /**
     * @var DBRequest
     */
     private $db;
      /**
     * @var LoggerInterface
     */
     private $logger;
     public function __construct(DBrequest $bdRequest,LoggerInterface $logger){
        $this->logger = $logger;
        $this->db = $bdRequest;
        parent::__construct();

    }
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
        
        //get account params
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $account=$this->db->findAccountByUsername($username);
        $tags= $input->getArgument('tags');
        $settings=$account->getSettings();
        $blacklist=$account->getBlacklist();
        
        
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
            $cpt=0;
            $rankToken = Signatures::generateUUID();
            do {
             
            $feed = $ig->hashtag->getFeed($tag, $rankToken, $maxId);
            dump($feed);
            foreach ($feed->getItems() as $item ) {
             $instaId=$item->getUser()->getPk(); 
             $username=$item->getUser()->getUsername();
             //check if blacklisted 
                

             array_push($users,array("id"=>$instaId,"username"=>$username )) ;
             
               }
              $maxId=$feed->getNextMaxId();
              sleep(2);
              $cpt++;
            }while ( $maxId !== null && 1>$cpt);
           
           
           
            
             $this->db->addPeople($account,$users);
        }
        

     
    }

   

    
}