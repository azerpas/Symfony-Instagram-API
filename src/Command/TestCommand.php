<?php

// src/Command/LikeAndFollowUsersCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\DBRequest;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Process\Process;



class TestCommand extends Command
{
    protected static $defaultName = 'test:main';

    /**
     * @var DBRequest
     */
    private $db;

    private $entityManager;

    public function __construct(DBrequest $dbRequest,EntityManagerInterface $entityManager){
        $this->db = $dbRequest;
        $this->entityManager=$entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Testing main command')
            ->addArgument('username', InputArgument::REQUIRED, 'My username')
        ;
    }

    private function isTime($account){
        $slots = json_decode($account->getSlots());
        if($slots==null)return false;
        $time=new \DateTime('@'.strtotime('now'));
        return $slots[$time->format('H')];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /////// CONFIG ///////
        $debug          = false;
        $truncatedDebug = false;
        //////////////////////
        ///
        /*
        $username = $input->getArgument('username');
        $user = $this->db->getUser($username);
        $accounts = $user->getAccounts();
        foreach($accounts as $account){
            $slots = $account->getSlots();
            $output->writeln($this->isTime($slots,$output)? 'yes for account: '.$account->getUsername():'no');
            if($this->isTime($slots,$output)){
                $output->writeln("Account within range, launching functions");
            }
            else{
                $output->writeln("Account not in range");
                continue;
            }
            // if people > 40 : no research
            // anyway: like/follow 20 accounts
            //elseif(isTime($slots)){}
            //elseif(!isTime($slots)){}
        }
        */
        $accounts = $this->entityManager->getRepository('App\Entity\Account')->findAll();
        foreach($accounts as $account){
            if($account->getStatus() && $this->isTime($account)){
                $output->writeln("enabled ".$account->getUsername());
                try {
                    //$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
                    //$ig->login($account->getUsername(), $account->getPassword());
                    $command = 'php bin/console insta:instance '.$account->getUsername().' '.$account->getPassword();
                    $process = new Process($command);
                    $process->start();
                    $process->setTimeout(10000);
                    /*
                    $process->run(function ($type, $buffer) {
                        if (Process::ERR === $type) {
                            throw  new \Exception('Error while trying to login');
                        } else {
                            echo 'OUT > '.$buffer;
                        }
                    });
                    //*/
                }
                catch (\Exception $e) {
                    $output->writeln("Error");
                    throw new \Exception('Something went wrong: ' . $e->getMessage());
                }
            }
        }

    }
}