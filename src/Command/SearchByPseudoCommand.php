<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use InstagramAPI\Request\Hashtag;
use InstagramAPI\Signatures;
use App\Entity\Account;
use App\Entity\User;
use App\Entity\People;
use App\Entity\History;
use Doctrine\ORM\EntityManagerInterface;

class SearchByPseudoCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'search:pseudo';

    protected $genderCheck = null;
    
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
       
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
        $output->writeln("Current blacklist: ".implode($blacklist));
        foreach ($users as $user){
            $output->writeln("Checking if username does not contains blacklisted word...");

            foreach ($blacklist as $keyword){
                if(strstr($user,$keyword)){
                    $output->writeln("Blacklisted word: ".$blacklist." for ".$user.". Going to next one.");
                    continue;
                }
            }
            $output->writeln("Scraping from @".$user);
            $output->writeln("Getting user ID");
            $userId = $ig->people->getUserIdForName($user);
            $followersList = $ig->people->getFollowers($userId, \InstagramAPI\Signatures::generateUUID());
            $output->writeln("Successfully fetched user followers");
            $nbOfFollowers = sizeof($followersList->getUsers());
            $count = 1;
            $valid = 0;
            $maxId = null;
            do{
                $followersList = $ig->people->getFollowers($userId, \InstagramAPI\Signatures::generateUUID(),null,$maxId);
                foreach ($followersList->getUsers() as $follower) {
                    $output->writeln($count."/".$nbOfFollowers." | Adding @".$follower->getUsername(). ", sleeping first...");
                    $output->writeln('valid: '.$valid);
                    // TODO check if already in DATABASE
                    $exist=$this->entityManager->getRepository('App\Entity\People')->findOneByUsername($follower->getUsername(),$account);
                    if($exist!=null){
                        $output->writeln("Already in database");
                        $count++;
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
                    if($valid >= 400){
                        $output->writeln("Enough users found");
                        $this->end($peoples,$account);
                        break;
                    }
                }
                $maxId = $followersList->getNextMaxId();
            }while($maxId !== null);
            $this->end($peoples,$account);
        }
    }

    public function end($peoples,$account)
    {
        foreach ($peoples as $user) {
            $exist=$this->entityManager->getRepository('App\Entity\People')->findOneByInstaId($user["id"],$account->getId());
            if($exist==null) {
                $person=new People($user["username"],$user["id"],$account);
                echo json_encode($person);
                $this->entityManager->persist($person);
                $account->addPerson($person);
                $this->entityManager->persist($account);
                $this->entityManager->flush();
            }
        }
        $history = new History();
        $history->setType("foundPeople");
        $history->setMessage("Found ".count($peoples). " people to Interact with !");
        $history->setFromAccount($account);
        $history->setDate(new \DateTime());
        $this->entityManager->persist($history);
        $this->entityManager->flush();
        exit();
    }
    /**
     * @method check if the user matches with the params
     * @param param account configuration
     * @param userInfo
     * @return boolean
     */
    private function UserMatch($settings, $userInfo, OutputInterface $output)
    {
        if(json_decode($userInfo->getUser())->is_private){
            $output->writeln("Private account");
            return false;
        }
        if(($userInfo->getUser()->getFollowerCount() > $settings->minfollow) && ($userInfo->getUser()->getFollowerCount() < $settings->maxfollow)){
            $output->writeln("Followers test passed\n");
            if (($userInfo->getUser()->getFollowingCount() > $settings->minfollowing) && ($userInfo->getUser()->getFollowingCount() < $settings->maxfollowing)){
                $output->writeln("Following test passed\n");
                if(($userInfo->getUser()->getMediaCount() > $settings->minpublication) && ($userInfo->getUser()->getMediaCount() < $settings->maxpublication)){
                    $output->writeln("Media test passed\n");
                    $output->writeln("Every tests passed!");
                    return true;
                }
            }
            else{
                $output->writeln($userInfo->getUser()->getFollowingCount() .'>'. $settings->minfollowing . ' -- ' . $userInfo->getUser()->getFollowingCount() .'<'. $settings->maxfollowing);
            }
        }
        $output->writeln($userInfo->getUser()->getFollowerCount() .'>'. $settings->minfollowing . ' -- ' . $userInfo->getUser()->getFollowerCount() .'<'. $settings->maxfollowing);
        return false;
    }

}