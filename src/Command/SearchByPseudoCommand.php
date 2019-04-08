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

class SearchByPseudoCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'search:pseudo';

    /**
     * @var DBRequest
     */
    private $db;

    public function __construct(DBrequest $bdRequest)
    {
        $this->db = $bdRequest;
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
        $account = $this->db->findAccountByUsername($username);

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
            $output->writeln("Getting user ID");
            $userId = $ig->people->getUserIdForName($user);
            $followersList = $ig->people->getFollowers($userId, \InstagramAPI\Signatures::generateUUID());
            $output->writeln("Successfully fetched user followers");
            foreach ($followersList->getUsers() as $follower) {
                sleep(rand(2,5));
                $userInfo = $ig->people->getInfoByName($follower->getUsername());
                $output->writeln("Checking before follow");
                if ($this->UserMatch($settings, $userInfo)){
                    $output->writeln("Following...");
                    array_push($peoples,array("id"=>$follower->getPk(),"username"=> $follower->getUsername()));
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
    private function UserMatch($settings, $userInfo)
    {
        //return true;

        if( ($userInfo->getUser()->getFollowerCount() > $settings->minfollow) ||
            ($userInfo->getUser()->getFollowerCount() < $settings->maxfollow)||
            ($userInfo->getUser()->getFollowingCount() > $settings->minfollowing)||
            ($userInfo->getUser()->getFollowingCount() < $settings->maxfollowing)||
            ($userInfo->getUser()->getMediaCount() > $settings->minpublication)||
            ($userInfo->getUser()->getMediaCount() < $settings->maxpublication)
            // ($param->private == 0 && $userInfo->getUser()->getIsPrivate()  )||
            //  ($param->picture ==-1 &&  $userInfo->getUser()->hasAnonymousProfilePicture())
        ){
            return true;
        }
        else{
            return false;
        }
    }

}