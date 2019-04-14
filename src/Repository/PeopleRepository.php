<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\People;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method People|null find($id, $lockMode = null, $lockVersion = null)
 * @method People|null findOneBy(array $criteria, array $orderBy = null)
 * @method People[]    findAll()
 * @method People[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeopleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, People::class);
    }

    // /**
    //  * @return People[] Returns an array of People objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /**
     * @param $account
     * @return People []
     */
    public function findAllByAccount($account)
    {  
        return $this->createQueryBuilder('p')
            ->andWhere('p.account = :acc')
            ->setParameter('acc', $account)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param $account
     * @return People []
     */
    public function findAllUsernameByAccount($account)
    {  
        return $this->createQueryBuilder('p')
            ->select('p.username')
            ->andWhere('p.account = :acc')
            ->setParameter('acc', $account)
            ->getQuery()
            ->getResult()
        ;
    }
    
    /**
     * @param $account
     * @return People [] where to_follow==true
     */
    public function findPeopleToFollowTrueByAccount($account)
    {  
        return $this->createQueryBuilder('p')
            ->andWhere('p.account = :acc')
            ->andWhere('p.to_follow = :enabled')
            ->setParameter('acc', $account)
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param $account
     * @return People [] 
     */
    public function findPeopleToContactByAccount($account)
    {  
        return $this->createQueryBuilder('p')
            ->andWhere('p.account = :acc')
            ->andWhere('p.is_following_back = :followBack')
            ->andWhere('p.contacted = :contacted')
            ->setParameter('acc', $account)
            ->setParameter('followBack', true)
            ->setParameter('contacted', false)
            ->getQuery()
            ->getResult()
        ;
    }
    
    /**
     * @param $account
     * @return People [] where to_follow==false
     */
    public function findPeopleToFollowFalseByAccount($account)
    {  
        return $this->createQueryBuilder('p')
            ->andWhere('p.account = :acc')
            ->andWhere('p.to_follow = :disabled')
            ->setParameter('acc', $account)
            ->setParameter('disabled',false)
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findOneByUsername($username,$account): ?People
    {   
        return $this->createQueryBuilder('p')
            ->andWhere('p.username = :user')
            ->andWhere('p.account = :acc')
            ->setParameter('user', $username) 
            ->setParameter('acc', $account)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    public function findOneByInstaId($instaID,$account): ?People
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.instaId = :val')
            ->andWhere('p.account = :acc')
            ->setParameter('val', $instaID)
            ->setParameter('acc', $account)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
}
