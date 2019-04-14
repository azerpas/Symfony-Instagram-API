<?php

namespace App\Service;
use App\Entity\Account;
use App\Entity\User;
use App\Entity\People;
use App\Entity\History;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use stdClass;

class DBRequest{
    protected $em;
    protected $lg;
    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger){
        $this->em = $entityManager;
        $this->lg = $logger;
    }


    /**
     * @method: assign instagram instance to user or create it if not exist
     */
    public function assignInstagramAccount(User $user,$account){
        $key = $user->getAccounts()->count();
        $user->setAccount($key,$account);
        $this->em->persist($user);
        $this->em->flush();
        $this->lg->info('pushed to users_accounts table (ManyToMany)');
    }

    /**
     * @method: create instagram(account) instance
     */
    public function createInstagramAccount(User $user,Account $account){
        $account->setSlots(json_encode(array_fill(0, 24, false)));
        $account->setUser(0,$user); // here
        $searchSettings = new stdClass();
        $searchSettings->pseudos = [];
        $searchSettings->hashtags = [];
        $searchSettings->blacklist = [];
        $account->setSearchSettings(serialize($searchSettings));
        $key = $account->getUsers()->count();
        $account->setUser($key,$user);
        $this->em->persist($account);
        $this->em->flush();
    }

    /**
     * @method add catched user list to people table
     * @param account
     * @param people list of instagram users
     * @return
     * @throws
     */
    public function addPeople($account,$people){
        foreach ($people as $user) {
            if(!$this->personExist($account,$user["id"])) {
                $person=new People();
                $person->setUsername($user["username"]);
                $person->setInstaId($user["id"]);
                $person->setToFollow(true);
                $person->setFollowDate(new \DateTime('@'.strtotime('now')));
                $person->setIsFollowingBack(false);
                $person->setNbFollowers(0);
                $person->setAccount($account);
                $person->setUpdated(new \DateTime('@'.strtotime('now')));
                echo json_encode($person);
                $this->em->persist($person);  
                $this->em->flush();
                $account->addPerson($person); 
                $this->em->persist($account);  
                $this->em->flush();
            }
        }
        $history = new History();
        $history->setType("foundPeople");
        $history->setMessage("Found ".count($people). " people to Interact with !");
        $history->setFromAccount($account);
        $history->setDate(new \DateTime());
        $this->em->persist($history);
        $this->em->flush();
    }

   
   

    /**
     * @method check if user exist in people table
     * @param $account
     * @param $user
     * @return boolean false  if not exist/ true if exist
     */
    public function personExist($account,$instaID)
    {
        $insta=$this->em->getRepository('App\Entity\People')->findOneByInstaId($instaID,$account->getId());
        if($insta != null) return true;
        return false;

    }

  
}