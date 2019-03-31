<?php

namespace App\Service;


use http\Env\Response;
use Psr\Log\LoggerInterface;
use App\Service\DBRequest;;
use Symfony\Component\HttpFoundation\JsonResponse;

class InstaInterface{
     /**
     * @var DBRequest
     */
    private $db;
     /**
     * @var LoggerInterface
     */
    private $logger;
    protected $bd;
    protected $lg;
    public function __construct(DBrequest $bdRequest,LoggerInterface $logger){
        $this->logger = $logger;
        $this->db = $bdRequest;
        parent::__construct();

    }
    /**
     * @method search instagram users 
     * @param account
     * @param tags 
     */


    protected function searchByTag($account)
    {    /////// CONFIG ///////
        $debug          = true;
        $truncatedDebug = true;
        //////////////////////
        $username = $account->getUsername();
        $password = $account->getPassword();
        //$tags=$account->getTags();
        $tags=array("chat") ;
        
        //$this->logger->info('Waking up the sun');
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
             
            foreach ($feed->getItems() as $item ) {
              
             array_push($users,array("id"=>$item->getUser()->getPk(),"username"=> $item->getUser()->getUsername())) ;
             
               }
              $maxId=$feed->getNextMaxId();
              sleep(2);
              $cpt++;
            }while ( $maxId !== null && 1>$cpt);
            
             $this->db->addPeople($account,$users);
        }

    }


}    