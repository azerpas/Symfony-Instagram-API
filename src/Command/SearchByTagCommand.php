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
use App\Service\DBRequest;
use Psr\Log\LoggerInterface;
class SearchByTagCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'search:tag';
    /**
     * @var DBRequest
     */
    private $db;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(DBrequest $bdRequest, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->db = $bdRequest;
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
        $account = $this->db->findAccountByUsername($username);


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

        foreach ($tags as $tag) {
            $maxId = null;
            $users = [];
            $cpt = 0;
            $rankToken = Signatures::generateUUID();
            do {
                $feed = $ig->hashtag->getFeed($tag, $rankToken, $maxId);

                foreach ($feed->getItems() as $item) {
                    $instaId = $item->getUser()->getPk();
                    $username = $item->getUser()->getUsername();
                    // in comment cause it make the process go over-timeout
                    sleep(rand(0.1,0.9));
                    $userInfo = $ig->people->getInfoByName($username);
                    // check if users settings (min follow etc...), match with current account
                    if ($this->UserMatch($settings, $userInfo))
                        array_push($users, array("id" => $instaId, "username" => $username));

                }

                $maxId = $feed->getNextMaxId();
                sleep(1);
                $cpt++;
            } while ($maxId !== null && 1 > $cpt);

            $this->db->addPeople($account, $users);

        }


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