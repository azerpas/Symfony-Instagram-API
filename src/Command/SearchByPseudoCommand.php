<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use InstagramAPI\Request\Hashtag;
use InstagramAPI\Signatures;
use App\Service\DBRequest;
use Doctrine\ORM\EntityManagerInterface;

class SearchByPseudoCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'search:pseudo';

    /**
     * @var DBRequest
     */
    private $db;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager,DBrequest $bdRequest)
    {
        $this->db = $bdRequest;
        $this->entityManager=$entityManager;
        parent::__construct();

    }
    protected function configure()
    {
        $this
            ->setName('search:pseudo')
            ->setDescription('Search intagram account') 
            ->addArgument('username', InputArgument::REQUIRED, 'My username')
            ->addArgument('password', InputArgument::REQUIRED, 'My password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {    /////// CONFIG ///////
        $debug          = false;
        $truncatedDebug = false;
        //////////////////////
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $account = $this->entityManager->getRepository('App\Entity\Account')->findOneByUsername($username);
       

        //get account params
        $settings = json_decode($account->getSettings());
        $blacklist = $account->getBlacklist();
        $users = unserialize($account->getSearchSettings())->pseudos;
        
       
        //get instagram instance
        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug); 
        try {
            $ig->login($username, $password);
          
        } catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }
        $peoples = [];
        foreach ($users as $user){
            $output->writeln("Scraping from @".$user);
            $output->writeln("Getting user ID");
            $userId = $ig->people->getUserIdForName($user);
            $followersList = $ig->people->getFollowers($userId, \InstagramAPI\Signatures::generateUUID());
            $output->writeln("Successfully fetched user followers");
            $nbOfFollowers = sizeof($followersList->getUsers());
            $count = 1;
            $valid = 0;
            foreach ($followersList->getUsers() as $follower) {
                $output->writeln($count."/".$nbOfFollowers."| Adding @".$follower->getUsername(). ", sleeping first...");

                // TODO check if already in DATABASE
                $exist=$this->entityManager->getRepository('App\Entity\People')->findOneByUsername($follower->getUsername(),$account);
                if($exist!=null){
                    $output->writeln("Already in database");
                    continue;
                }

                sleep(rand(2,5));
                try{
                    $userInfo = $ig->people->getInfoByName($follower->getUsername());
                    $output->writeln("Checking before adding...");
                }catch (\Exception $e){
                    $output->writeln($e->getMessage());
                    continue;
                }


                if ($this->UserMatch($settings, $userInfo,$output)){
                    $output->writeln("Pushing...");
                    $valid++;
                    array_push($peoples,array("id"=>$follower->getPk(),"username"=> $follower->getUsername()));
                }
                $output->writeln("");
                $count++;
                if($valid >= 80){
                    $output->writeln("Enough users found");
                    break;
                }
            }
             
        }
        $this->db->addPeople($account, $peoples);
         
    }

    /**
     * @method check if the user matches with the params
     * @param param account configuration
     * @param userInfo
     * @return boolean
     */
    private function UserMatch($settings, $userInfo, OutputInterface $output)
    {
        if(($userInfo->getUser()->getFollowerCount() > $settings->minfollow) && ($userInfo->getUser()->getFollowerCount() < $settings->maxfollow)){
            $output->writeln("Followers test passed");
            if (($userInfo->getUser()->getFollowingCount() > $settings->minfollowing) && ($userInfo->getUser()->getFollowingCount() < $settings->maxfollowing)){
                $output->writeln("Following test passed");
                if(($userInfo->getUser()->getMediaCount() > $settings->minpublication) && ($userInfo->getUser()->getMediaCount() < $settings->maxpublication)){
                    $output->writeln("Media test passed");
                    return true;
                }
                return false;
            }
            return false;
        }
        return false;
    }

}