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
     */
    public function setParams($user,$params){
        $account = new Account();
        $account = $user->getAccounts();
        if($account==null){
            return new JsonResponse(array('message' => 'No Instagram accounts were assigned for this account'), 419);
        }
        $account->setSettings(json_encode($params));
        $this->em->persist($account);
        $this->em->flush();
        return "Success";
    }
    /**
     * @method assign instagram instance to user or create it if not exist
     */
    public function assignInstagramAccount($user,$account,$username,$password){
        if($account == null){
            //not exist
            $account=new Account();
            $account->setUsername($username);
            $account->setPassword($password);
            $account->setUserId($user);
            $this->em->persist($account);
            $this->em->flush();
        }
        //exist
        $user->setAccounts($account);
        $this->em->persist($user);
        $this->em->flush();
    }

}