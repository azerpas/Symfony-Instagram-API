<?php

// src/Command/LikeAndFollowUsersCommand.php
namespace App\Command;

use InstagramAPI\Response\Model\FriendshipStatus;
use mysql_xdevapi\Exception;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Psr\Log\LoggerInterface;
use App\Entity\History;

class LikeAndFollowNewCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:likeAndFollowNew';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger){
        $this->logger = $logger;
        $this->em = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Like medias and follow Instagram users from the People table for an account')
            ->addArgument('username', InputArgument::REQUIRED, 'My username')
            ->addArgument('password', InputArgument::REQUIRED, 'My password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /////// CONFIG ///////
        $debug          = false;
        $truncatedDebug = false;
        //////////////////////
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
        try {
            $account = $this->em->getRepository('App\Entity\Account')->findOneByUsername($username);
            $peopleToInteract = $this->em->getRepository('App\Entity\People')->findPeopleToFollowTrueByAccount($account);
            $nbOfFollows = json_decode($account->getSettings())->followPerHour;
            intval($nbOfFollows) ? $output->writeln("Number of accounts to Interact with: ".intval($nbOfFollows))  : $output->writeln("You put 0 follow per hour\n");
            $likeCommand = $this->getApplication()->find('app:like');
            $followCommand = $this->getApplication()->find('app:follow');
            $output->writeln('Login...');
            $ig->login($username, $password);
            $counter = 0;
            while ($counter<20 && $counter<sizeof($peopleToInteract)-1 || $counter>=$nbOfFollows) {
                $output->writeln($counter." / ".sizeof($peopleToInteract));
                $person = $peopleToInteract[$counter];
                $mediaIds = [];
                $mediaUrls = [];
                try {
                    $output->writeln('Getting user feed...');
                    $feed = json_decode($ig->timeline->getUserFeed($person->getInstaId()));
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                    $this->errUser($person,$account);
                    $counter++;
                    continue;
                }
                $randomArr = range(0,sizeof($feed->items));
                shuffle($randomArr);
                var_dump(sizeof($feed));
                $mediaIds = [];
                $mediaUrls = [];
                // TODO check for gender etc... put a loop cause it won't work with for boucle
                for($y=0;$y<2;$y++){
                    $i = $randomArr[$y];
                    $output->writeln($i);
                    $flag = false;
                    /*
                    while(!$flag){
                        // TODO dynamic like count
                        $feed->items[$i]->like_count > 30 ? $flag = true : shuffle($randomArr);
                        if(!$flag){
                            $output->writeln('Not enough likes: '.$feed->items[$i]->like_count);
                            var_dump($randomArr);
                            $i = $randomArr[$y];
                        }
                    }
                    */
                    try{
                        array_push($mediaIds,$feed->items[$i]->id);
                    }catch (\Exception $e){
                        try{
                            shuffle($randomArr);
                            $i = $randomArr[$y];
                        }catch (\Exception $p){
                            $output->writeln($e);
                            $output->writeln($p);
                            continue;
                        }
                    }
                    switch ($feed->items[$i]->media_type){
                        case '1': // IMAGE
                            array_push($mediaUrls,$feed->items[$i]->image_versions2->candidates[0]->url);
                            break;
                        case '2': // VIDEO
                            array_push($mediaUrls,$feed->items[$i]->video_versions[0]->url);
                            break;
                        case '8': // MULTIPLE CONTENT
                            if(($feed->items[$i]->carousel_media[0]->media_type)=='1') {
                                array_push($mediaUrls,$feed->items[$i]->carousel_media[0]->image_versions2->candidates[0]->url);
                                break;
                            }
                            else if(($feed->items[$i]->carousel_media[0]->media_type)=='2') {
                                array_push($mediaUrls,$feed->items[$i]->carousel_media[0]->video_versions[0]->url);
                                break;
                            }
                    }
                }

                $output->writeln('Liking...');

                for($likes = 0;$likes<sizeof($mediaIds);$likes++) {
                    $likeCommandArguments = [
                        'command' => 'app:like',
                        'username' => $username,
                        'password' => $password,
                        'mediaId' => $mediaIds[$likes],
                    ];
                    $likeInput = new ArrayInput($likeCommandArguments);
                    $likeCommand->run($likeInput, $output);
                    $historyLike = new History();
                    $historyLike->setType("like");
                    $historyLike->setMessage('Liked a media of @' . $person->getUsername());
                    $historyLike->setInteractWith($person);
                    $historyLike->setLink($mediaUrls[$likes]);
                    $historyLike->setFromAccount($account);
                    $historyLike->setDate(new \DateTime());
                    $this->em->persist($historyLike);
                    sleep(5);
                }

                if (intval($nbOfFollows)) {
                    $output->writeln('following...');
                    $followCommandArguments = [
                        'command' => 'app:follow',
                        'username' => $username,
                        'password' => $password,
                        'userId' => $person->getInstaID(),
                    ];
                    $followInput = new ArrayInput($followCommandArguments);
                    sleep(rand(3, 6));
                    $followCommand->run($followInput, $output);

                    // TODO : need to add CATCH
                    $historyFollow = new History();
                    $historyFollow->setType("follow");
                    $historyFollow->setMessage("Followed @" . $person->getUsername());
                    $historyFollow->setInteractWith($person);
                    $historyFollow->setFromAccount($account);
                    $historyFollow->setDate(new \DateTime());
                    $this->em->persist($historyFollow);
                }

                $person->setToFollow(false);
                $person->setUpdated(new \DateTime());

                $this->em->persist($person);
                $this->em->flush();
                $output->writeln('sleeping...');
                sleep(15);
                $counter++;
            }

            $historyEnd = new History();
            $historyEnd->setType('bot');
            $historyEnd->setMessage('Bot ended following and liking, waiting till next valid hour...');
            $historyEnd->setFromAccount($account);
            $historyEnd->setDate(new \DateTime());
            $this->em->persist($historyEnd);
            $this->em->flush();
        }
        catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }
    }

    // Function to check string starting
    // with given substring
    private function startsWith ($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    // Function to check the string is ends
    // with given substring or not
    private function endsWith($string, $endString)
    {
        $len = strlen($endString);
        if ($len == 0) {
            return true;
        }
        return (substr($string, -$len) === $endString);
    }

    private function errUser($person,$account){
        $historyError = new History();
        $historyError->setType("error");
        $historyError->setMessage('We got an error while trying to get infos from: '. $person->getUsername().'. <br/> Removing and getting to next user');
        $historyError->setFromAccount($account);
        $historyError->setDate(new \DateTime());
        $this->em->persist($historyError);
        $this->em->remove($person);
        $this->em->flush();
    }
}