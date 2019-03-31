<?php

namespace App\Service;
use App\Entity\Account;
use App\Entity\User;
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
     * @return JsonResponse
     */
    public function setParams($user,$params){
       
        $account=$user->getAccount(1);
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
    * @return JsonResponse
    */
    public function setSlot(User $user,$slot,$value){
        $account=$user->getAccount(0);
        if($account==null) return new JsonResponse(array('message' => 'no Instagram account asigned for this account '), 419);


        $slots=unserialize($account->getSlots());
        $slots[$slot]=$value;
        $this->lg->debug($value);


        $account->setSlots(serialize($slots));
        $this->em->persist($account);
        $this->em->flush();
        return $account;

    }

    /**
     * @method getSlots slots list
     * @param user  user  entity object
     * @return Account::slots
     */
    public function getSlots(User $user,LoggerInterface $logger){

        $accounts=$user->getAccounts();
        $logger->info($accounts->count());
        for($i = 0; $i<$accounts->count(); $i++){
            $logger->info(serialize($accounts->get($i)->getSlots()));
        }
        if($accounts==null) return null;
        $logger->info('not null');
        $logger->info(unserialize($accounts[0]->getSlots()));
        // ?????? each account created got $slots null per default
        // we need to setSlots() in DBRequest::createInstagramAccount()
        return $slots= unserialize($accounts[0]->getSlots());
    }


    /**
     * @method edit Profile
     * @param User
     * @param User::password user password
     * @param User::email
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
    public function assignInstagramAccount(User $user,$account,$username,$password, LoggerInterface $logger){
        $key = $user->getAccounts()->count();
        $user->setAccount($key,$account);
        $this->em->persist($user);
        $this->em->flush();
        $logger->info('pushed to users_accounts table (ManyToMany)');
    }

    /**
     * @method: create instagram(account) instance
     */
    public function createInstagramAccount(User $user,Account $account){
        $account->setUsername($account->getUsername());
        $account->setPassword($account->getPassword());
        $key = $account->getUsers()->count();
        $account->setUser($key,$user);
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
}