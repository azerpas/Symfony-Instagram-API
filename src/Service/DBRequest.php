<?php

namespace App\Service;
use App\Entity\Account;
use App\Entity\User;
use App\Entity\People;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class DBRequest{
    protected $em;
    protected $lg;
    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger){
        $this->em = $entityManager;
        $this->lg = $logger;
    }
    /**
     * @method set bot params in account entity
     * @param user  user  entity object
     * @param params parameters array
     */

   /**
    * @method set bot params in account entity
    * @param user  user  entity object
    * @param params parameters array
    */
    public function setParams($user,$params){
       
        $account=$user->getAccount(0);
        if($account==null) return new JsonResponse(array('message' => 'no Instagram account asigned for this account '), 419);
        $account->setSettings(json_encode($params));
        $this->em->persist($account);
        $this->em->flush();
        return  new JsonResponse(array('message' => 'success'), 200);
    }


       /**
        * @method set slot status
        * @param user  user  entity object
        * @param slot time slot
        * @param value on/off
        */
        public function setSlot($user,$slot,$value){
            $account=$user->getAccount(0);
            if($account==null) return new JsonResponse(array('message' => 'no Instagram account asigned for this account '), 419);


            $slots=json_decode($account->getSlots());
            if($value=="off") $slots[$slot]=false;
            else $slots[$slot]=true;
            $this->lg->info($value."   ".json_encode($slots).$slot );
              
           // $slots=array_fill(0, 24, false);
            $account->setSlots(json_encode($slots));
            $this->em->persist($account);
            $this->em->flush();
            return $account;

           }

         /**
        * @method get slots list
        * @param user  user  entity object
        */
        public function getSlots($user){
           
            $account=$user->getAccount(0);
          
            if($account==null) return null;

           // return  array_pad(array(), 24, false);
            return  json_decode($account->getSlots());
           }


       /**
    * @method edit Profile
    * @param user
    * @param pwd user password
    * @param email
    */
    public function editProfile($user,$pwd,$email){

        if(strlen($email)!=0)$user->setEmail($email);
        if(strlen($pwd)!=0)$user->setPassword($pwd);
        $this->em->persist($user);
        $this->em->flush();
       }

    /**
     * @method: assign instagram instance to user or create it if not exist
     */
    public function assignInstagramAccount(User $user,$account,$username,$password){
        $user->setAccount(0,$account);
        $this->em->persist($user);
        $this->em->flush();
        $this->lg->info('pushed to users_accounts table (ManyToMany)');
    }

    /**
     * @method: create instagram(account) instance
     */
    public function createInstagramAccount(User $user,Account $account){
        $account->setUsername($account->getUsername());
        $account->setPassword($account->getPassword());
        $account->setSlots(json_encode(array_fill(0, 24, false)));
        $account->setUser(0,$user); // here
        $this->em->persist($account);
        $this->em->flush();
    }

     /**
    * @method
    * @param status on/off
    * @return
    */
    public function setStatus($user,$status)
    {

        $account=$user->getAccount(0);
        if($account==null) return new JsonResponse(array('message' => 'no Instagram account asigned for this user '), 419);
        if($status == "true") $account->setStatus(true);
        else $account->setStatus(false);
        $this->em->persist($account);
        $this->em->flush();
        return  true;


    }
      /**
    * @method
     *@param user
    * @return
    */
     public function getStatus($user)
    {
        $account=$user->getAccount(0);
        if($account==null) return new JsonResponse(array('message' => 'no Instagram account asigned for this user '), 419);
        return $account->getStatus();
    }

       /**
    * @method add catched user list to people table
     *@param account
     *@param people list of instagram users 
     * @return
    */
     public function  addPeople($account,$people)
    { 
        foreach ($people as $user)
        { 
           if(!$this->personExist($account,$user["id"]))
              { $person=new People();
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
    /**
     * @method
     * @param
     * @return User 
     */
    public function getUser($username){
        return $this->em->getRepository('App\Entity\User')->findOneByUsername($username); 
    }

    /**
     * @method get all accounts list
     * @return account[] list of all accounts 
     */
    public function getAllAccounts(){
        return $this->em->getRepository('App\Entity\Account')->findAll();
    }
    /**
     * @method get People list 
     * @return People[] list of People 
     */
    public function getAllPeopleForAccount($account){
        return $this->em->getRepository('App\Entity\People')->findAllByAccount($account);
    }

}