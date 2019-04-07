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
            //$output->writeln('Account to interact with : '.$account->getUsername());
            $peopleToInteract = $this->em->getRepository('App\Entity\People')->findAllByAccount($account);
            $likeUserMediasCommand = $this->getApplication()->find('app:likeUserMedias');
            $followCommand = $this->getApplication()->find('app:follow'); 
            foreach($peopleToInteract as $person) {
                //$output->writeln($person->getUsername().' '.$person->getInstaID());
                $likeUserMediasArguments = [
                    'command' => 'app:likeUserMedias',
                    'username' => $username,
                    'password' => $password,
                    'userId' => $person->getInstaID(),
                ];
                $likeUserMediasInput = new ArrayInput($likeUserMediasArguments);
                $likeUserMediasCommand->run($likeUserMediasInput, $output);

                // TODO : need to add CATCH
                /* $history directly into $likeUserMediasCommand
                $history = new History();
                $history->setType("like");
                $history->setFromAccount($account);
                $history->setMessage("Liked ".);
                */
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
                $history = new History();
                $history->setType("follow");
                $history->setFromAccount($account);
                $history->setMessage("Followed ". $person->getUsername());
                $this->em->persist($history);
                $this->em->flush();

                $this->em->getRepository('App\Entity\People')->findOneByInstaId($person->getInstaID(),$account)->setToFollow(false);
                $this->em->getRepository('App\Entity\People')->findOneByInstaId($person->getInstaID(),$account)->setUpdated(new \DateTime('@'.strtotime('now')));
                $this->em->persist($person);  
                $this->em->flush();
                //$output->writeln($person->getUsername().' followed correctly and updated in People table');
                sleep(30);
            }
        }
        catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }        
    }    
}