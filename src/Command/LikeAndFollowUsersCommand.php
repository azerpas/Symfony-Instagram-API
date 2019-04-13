<?php

// src/Command/LikeAndFollowUsersCommand.php
namespace App\Command;

use InstagramAPI\Response\Model\FriendshipStatus;
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
//use App\Service\DBRequest;

class LikeAndFollowUsersCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:likeAndFollowUsers';

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
            $ig->login($username, $password);
            $account = $this->em->getRepository('App\Entity\Account')->findOneByUsername($username);
            $peopleToInteract = $this->em->getRepository('App\Entity\People')->findPeopleToFollowTrueByAccount($account);
            $likeCommand = $this->getApplication()->find('app:like');
            $followCommand = $this->getApplication()->find('app:follow'); 
            $counter = 0;
            while ($counter<10 && $counter<sizeof($peopleToInteract)) {
                $person = $peopleToInteract[$counter];
                $mediaIds = [];
                $mediaUrls = [];
                try{
                    $feed = json_decode($ig->timeline->getUserFeed($person->getInstaId()));
                }catch (\Exception $e){
                    $output->writeln($e->getMessage());
                    $historyError = new History();
                    $historyError->setType("error");
                    $historyError->setMessage('We got an error while trying to get infos from: '. $person->getUsername().'. <br/> Removing and getting to next user');
                    $historyError->setInteractWith($person);
                    $historyError->setFromAccount($account);
                    $historyError->setDate(new \DateTime());
                    $this->em->persist($historyError);
                    $this->em->remove($person);
                    $this->em->flush();
                    $counter++;
                    continue;
                }
                for($i=0;$i<sizeof($feed->items);$i++) {
                    $mediaId = $feed->items[$i]->id;
                    array_push($mediaIds,$mediaId);
                    if(($feed->items[$i]->media_type)=='1') {
                        $mediaUrl = $feed->items[$i]->image_versions2->candidates[0]->url;
                        array_push($mediaUrls,$mediaUrl);
                    }
                    else if(($feed->items[$i]->media_type)=='2') {
                        $mediaUrl = $feed->items[$i]->video_versions[0]->url;
                        array_push($mediaUrls,$mediaUrl);
                    }
                    else if(($feed->items[$i]->media_type)=='8') {
                        if(($feed->items[$i]->carousel_media[0]->media_type)=='1') {
                            $mediaUrl = $feed->items[$i]->carousel_media[0]->image_versions2->candidates[0]->url;
                            array_push($mediaUrls,$mediaUrl);
                        }
                        else if(($feed->items[$i]->carousel_media[0]->media_type)=='2') {
                            $mediaUrl = $feed->items[$i]->carousel_media[0]->video_versions[0]->url;
                            array_push($mediaUrls,$mediaUrl);
                        }
                    }
                }
                $randomNumbers = [];
                for($i=0;$i<2;) {
                    $randomNumber=rand(1,sizeof($mediaIds));
                    if (in_array($randomNumber,$randomNumbers)==false) {
                        array_push($randomNumbers,$randomNumber);
                        $i++;
                    }
                }
                $likesNumber = 0;
                while ($likesNumber<2 && $likesNumber<sizeof($mediaIds)) {
                    $likeCommandArguments = [
                        'command' => 'app:like',
                        'username' => $username,
                        'password' => $password,
                        'mediaId' => $mediaIds[$randomNumbers[$likesNumber]-1],    
                    ];
                    $likeInput = new ArrayInput($likeCommandArguments);
                    $likeCommand->run($likeInput, $output);
                    $historyLike = new History();
                    $historyLike->setType("like");
                    $historyLike->setMessage('Liked a media (number '.($randomNumbers[$likesNumber]).') of @'. $person->getUsername());
                    $historyLike->setInteractWith($person);
                    $historyLike->setFromAccount($account);
                    $historyLike->setDate(new \DateTime());
                    $this->em->persist($historyLike);
                    sleep(5);
                    $likesNumber++;
                }

                $followCommandArguments = [
                    'command' => 'app:follow',
                    'username' => $username,
                    'password' => $password,
                    'userId' => $person->getInstaID(),    
                ];
                $followInput = new ArrayInput($followCommandArguments);
                sleep(rand(3,6));
                $followCommand->run($followInput, $output);
                
                // TODO : need to add CATCH
                $historyFollow = new History();
                $historyFollow->setType("follow");
                $historyFollow->setMessage("Followed @". $person->getUsername());
                $historyFollow->setInteractWith($person);
                $historyFollow->setFromAccount($account);
                $historyFollow->setDate(new \DateTime());
                $this->em->persist($historyFollow);
                
                $person->setToFollow(false);
                $person->setUpdated(new \DateTime());
                if ($likesNumber==2) {
                    $mediasArray = array (($mediaIds[$randomNumbers[0]-1])=>($mediaUrls[$randomNumbers[0]-1]), ($mediaIds[$randomNumbers[1]-1])=>($mediaUrls[$randomNumbers[1]-1]));
                    $person->setLikedMedias(json_encode($mediasArray));
                }
                else if ($likesNumber==1) {
                    $mediasArray = array (($mediaIds[$randomNumbers[0]-1])=>($mediaUrls[$randomNumbers[0]-1]));
                    $person->setLikedMedias(json_encode($mediasArray));
                }
                $this->em->persist($person); 
                $this->em->flush(); 
                sleep(30);
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

}