<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use InstagramAPI\Request\Direct;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Entity\Account;
use App\Entity\People;
class instaSendMessageCommand extends ContainerAwareCommand
{   protected static $defaultName = 'insta:contact';

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
            ->setName('insta:contact')
            ->setDescription('Send message to intagram accounts')
            ->addArgument('username',InputArgument::REQUIRED,'username?') 
            ->addArgument('password',InputArgument::REQUIRED,'password?')  
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
        try {
        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
        $ig->login($username, $password); 
       
        $account = $this->em->getRepository('App\Entity\Account')->findOneByUsername($username); 
        $peopleToInteract = $this->em->getRepository('App\Entity\People')->findPeopleToContactByAccount($account);
        $config=json_decode($account->getSettings()); 
        $dateToday = new \DateTime('@'.strtotime('now'));
        $timeUnit=60;
        if($config->Type=="h")$timeUnit=3600;
        else if($config->Type=="d")$timeUnit=3600*24;
        foreach( $peopleToInteract as $person)
        { if( ( $dateToday->getTimestamp()-$person->getUpdated()->getTimestamp() )  >= $config->waitingTime*$timeUnit ) 
           $ig->direct->sendText([ 'users' => [$person->getInstaId()]], $config->message);       
           $person->setContacted(true);
           $this->em->persist($person);
           $this->em->flush();
           $output->writeln($person->getContacted());
           sleep(5); 
        }   
            
        }
        catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }     
      



    }

   

    
}