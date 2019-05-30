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
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Account;
use App\Entity\User;
use App\Entity\People;
use App\Entity\History;
use Psr\Log\LoggerInterface;

class SearchByTagCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'search:tag';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->entityManager=$entityManager;
        parent::__construct();

    }

    protected function configure()
    {
        $this
            ->setName('search:tag')
            ->setDescription('Seach intagram account')
            ->addArgument('username', InputArgument::REQUIRED, 'My username')
            ->addArgument('password', InputArgument::REQUIRED, 'My password');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /////// CONFIG ///////
        $debug = false;
        $truncatedDebug = false;
        //////////////////////

        //getAccount
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $account = $this->entityManager->getRepository('App\Entity\Account')->findOneByUsername($username);
       


        //get account params
        $settings = json_decode($account->getSettings());
        $blacklist = $account->getBlacklist();
        $tags = unserialize($account->getSearchSettings())->hashtags;

        //get instagram instance
        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
        try {

            $ig->login($username, $password);

        } catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }

        $output->writeln("Current blacklist: ".implode($blacklist));
        foreach ($tags as $tag) {
            $maxId = null;
            $users = [];
            $cpt = 0;
            $rankToken = Signatures::generateUUID();
            do {
                $feed = $ig->hashtag->getFeed($tag, $rankToken, $maxId);
                $output->writeln("Scraping from #".$tag);
                $nbOfItems = sizeof($feed->getItems());
                $count = 1;
                $valid = 0;
                foreach ($feed->getItems() as $item) {
                    $output->writeln("Checking if description does not contains blacklisted word...");
                    foreach ($blacklist as $keyword){
                        if(strstr($item->caption->text,$keyword)){
                            $output->writeln("Blacklisted word: ".$blacklist." for ".$item->caption->text.". Going to next one.");
                            continue;
                        }
                    }

                    $instaId = $item->getUser()->getPk();
                    $username = $item->getUser()->getUsername();

                    $output->writeln($count."/".$nbOfItems." | Adding @".$item->getUser()->getUsername(). ", sleeping first...");

                    $exist=$this->entityManager->getRepository('App\Entity\People')->findOneByUsername($username,$account);
                    if($exist!=null){
                        $output->writeln($username.": Already in database");
                        continue;
                    }

                    $output->writeln("Adding @".$username. ", sleeping first...");
                    sleep(rand(2,5));
                    try{
                        $userInfo = $ig->people->getInfoByName($username);
                        // check if users settings (min follow etc...), match with current account
                        $output->writeln("Checking before adding...");
                    }catch (\Exception $e){
                        $output->writeln($e->getMessage());
                        continue;
                    }

                    if ($this->UserMatch($settings, $userInfo,$output)) {
                        $output->writeln("Pushing to array...");
                        $valid++;
                        array_push($users, array("id" => $instaId, "username" => $username));
                    }
                    $count++;
                    $output->writeln("");
                    if($valid >= 10){
                        $output->writeln("Enough users found");
                        break;
                    }
                }
                if($valid >= 10){
                    $output->writeln("Enough users found");
                    break;
                }
                $maxId = $feed->getNextMaxId();
                sleep(1);
                $cpt++;
            } while ($maxId !== null && 1 > $cpt);


            foreach ($users as $user) {
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
            $history->setMessage("Found ".count($users). " people to Interact with !");
            $history->setFromAccount($account);
            $history->setDate(new \DateTime());
            $this->entityManager->persist($history);
            $this->entityManager->flush();

        }


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
            $output->writeln("Followers test passed");
            if (($userInfo->getUser()->getFollowingCount() > $settings->minfollowing) && ($userInfo->getUser()->getFollowingCount() < $settings->maxfollowing)){
                $output->writeln("Following test passed");
                if(($userInfo->getUser()->getMediaCount() > $settings->minpublication) && ($userInfo->getUser()->getMediaCount() < $settings->maxpublication)){
                    $output->writeln("Media test passed");
                    $output->writeln("Every tests passed!");
                    return true;
                }
                return false;
            }
            return false;
        }
        return false;
    }
}