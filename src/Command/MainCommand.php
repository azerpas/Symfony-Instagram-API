<?php

namespace App\Command;

use Couchbase\Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;  
use Psr\Log\LoggerInterface;
use App\Entity\Account;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MainCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'insta:main';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
     /**
     * @var LoggerInterface;
     */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger){
        $this->logger = $logger;
        $this->entityManager=$entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('insta:main')
            ->setDescription('Main command ')    
        ;
    }

    /**
     * @method: check if user have defined time slots
     * if so fetching them from BD and checking if currently in time slot
     * @return Boolean
     * @throws
     */
    private function isTime($account){
        $slots = json_decode($account->getSlots());
        if($slots==null)return false;
        $time=new \DateTime('@'.strtotime('now'));
        return $slots[$time->format('H')];
    }

   
    protected function execute(InputInterface $input, OutputInterface $output){
        //accounts list        
        $accounts=$this->entityManager->getRepository('App\Entity\Account')->findAll();
       
       
        
        //process array
        $runningProcesses = [];

        foreach($accounts as $account){
            if($account->getStatus() && $this->isTime($account)){
                $output->writeln("Account: ".$account->getUsername());

        //command list
        $commands=array();
        array_push($commands,'php bin/console insta:instance '.$account->getUsername().' '.$account->getPassword());
        array_push($commands,'php bin/console search:tag '.$account->getUsername().' '.$account->getPassword());
        array_push($commands,'php bin/console app:likeAndFollowUsers '.$account->getUsername().' '.$account->getPassword()); 
                foreach($commands as $command){
                    try{
                        $process = new Process($command);
                        $process->setTimeout(6000);
                        $process->start(function ($type, $buffer) {
                            if (Process::ERR === $type) {
                                throw  new \Exception($type.': Error while trying to login :'.$buffer);
                            }
                        });
                        $runningProcesses[] = $process;
                    }catch (\Exception $e){
                        $output->writeln("Account could not connect");
                        continue; // if can't login than stop here
                    }   
                }  
            }  
        }
        $output->writeln("<info>#all processes are started </info>");    
        $output->writeln("<comment>waitting ...</comment>");    
            //wait 
            while (count($runningProcesses)) {
                foreach ($runningProcesses as $i => $runningProcess) {
                    // specific process is finished, so we remove it
                    if (! $runningProcess->isRunning()) {
                        unset($runningProcesses[$i]);
                    }
                    // check every second
                    $output->write("<comment>.</comment>");  
                    sleep(1);
                }
            }
        $output->writeln("<info>#successful execution...</info>");  
    return true;
    }
}