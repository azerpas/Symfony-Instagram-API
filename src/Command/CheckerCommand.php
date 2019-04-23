<?php

// src/Command/CheckerCommand.php
namespace App\Command;


use App\Entity\History;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use InstagramAPI\Response\Model\FriendshipStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;

class CheckerCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:checker';

    /**
    * @var LoggerInterface
    */
    private $logger;

    /**
    * @var EntityManagerInterface
    */
    private $em;    
    
    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger){
        $this->em = $entityManager;
        $this->logger = $logger;
        parent::__construct();
    }
    
    protected function configure()
    {
        $this 
        ->setDescription('Checker of People table')
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
            $peopleToInteract = $this->em->getRepository('App\Entity\People')->findPeopleToFollowFalseByAccount($account);
            $dateToday = new \DateTime('@'.strtotime('now'));
            //$output->writeln($dateToday->format('Y-m-d H:i:s'));
            $uuid  = \InstagramAPI\Signatures::generateUUID();
            $maxId = null;
            $selfFollowersArray = [];
            do {
                $response = $ig->people->getSelfFollowers($uuid, null, $maxId);
                foreach ($response->getUsers() as $item) {
                    $selfFollowersArray[] = $item->getUsername();
                }
                $maxId = $response->getNextMaxId();
                if ($maxId) {
                    //echo "Sleeping for 5s...\n";
                    sleep(5);
                }
            } while ($maxId !== null);
            $unfollowCommand = $this->getApplication()->find('insta:unfollow'); 
            $counter = 0;
            $unfollowCounter = 0;
            //$output->writeln(sizeof($peopleToInteract).' to interact with.');
            while ($unfollowCounter < 10 && $counter<sizeof($peopleToInteract)) {
                $person = $peopleToInteract[$counter];
                if (($person->getIsFollowingBack())==true) {
                    if ((in_array($person->getUsername(), $selfFollowersArray))==false) {
                        $unfollowArgument = [
                            'command' => 'insta:unfollow',
                            'username' => $username,
                            'password' => $password,
                            'userId' => $person->getInstaID(),
                        ];
                        $unfollowInput = new ArrayInput($unfollowArgument);
                        $unfollowCommand->run($unfollowInput, $output);
                        $historyUnfollow = new History();
                        $historyUnfollow->setType('unfollow');
                        $historyUnfollow->setMessage('Unfollowed @'. $person->getUsername().' because he unfollowed '.$account->getUsername());
                        $historyUnfollow->setInteractWith($person);
                        $historyUnfollow->setFromAccount($account);
                        $historyUnfollow->setDate(new \DateTime());
                        $this->em->persist($historyUnfollow);
                        //TO DO SETBLACKLIST
                        //$account->setBlacklist('@'.$person->getUsername());
                        $this->em->persist($account);
                        $this->em->remove($person);
                        $unfollowCounter++;
                        sleep(30);
                    }    
                }
                else {
                    if (((($person->getUpdated())->diff($dateToday))->days) >= 10) {
                        if (in_array($person->getUsername(), $selfFollowersArray)) {
                            $person->setIsFollowingBack(true);
                            $this->em->persist($person);
                        }
                        else {
                            $unfollowArgument = [
                                'command' => 'insta:unfollow',
                                'username' => $username,
                                'password' => $password,
                                'userId' => $person->getInstaID(),
                            ];
                            $unfollowInput = new ArrayInput($unfollowArgument);
                            $unfollowCommand->run($unfollowInput, $output);
                            $historyUnfollow = new History();
                            $historyUnfollow->setType('unfollow');
                            $historyUnfollow->setMessage('Unfollowed @'.$person->getUsername().' because he did not follow back '.$account->getUsername().' after 10 days.');
                            $historyUnfollow->setInteractWith($person);
                            $historyUnfollow->setFromAccount($account);
                            $historyUnfollow->setDate(new \DateTime());
                            $this->em->persist($historyUnfollow);
                            //TO DO SETBLACKLIST
                            //$account->setBlacklist('@'.$person->getUsername());
                            $this->em->persist($account);
                            $this->em->remove($person);
                            $unfollowCounter++;
                            sleep(30);
                        }
                    }
                }
                $this->em->flush();
                $counter++;
            }
            $historyChecker = new History();
            $historyChecker->setType('checker');
            $historyChecker->setMessage('Checker updated database with '.$counter.' People treated including '.$unfollowCounter.' unfollows.');
            $historyChecker->setFromAccount($account);
            $historyChecker->setDate(new \DateTime());
            $this->em->persist($historyChecker);
            $this->em->flush();
        }
        catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }
    }

}