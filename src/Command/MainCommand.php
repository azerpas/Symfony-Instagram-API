<?php

namespace App\Command;

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
     */
    private function isTime($account){
        $slots = json_decode($account->getSlots());
        if($slots==null)return false;
        $time=new \DateTime('@'.strtotime('now'));
        return $slots[$time->format('H')];
    }

   
    protected function execute(){
        $accounts=$this->entityManager->getRepository('App\Entity\Account')->findAll();
        foreach($accounts as $account){
            if($account->getStatus() && $this->isTime($account)){
                $commands=array();
                array_push($commands,'php bin/console search:tag '.$account->getUsername().' '.$account->getPassword());
                array_push($commands,'php bin/console app:likeAndFollowUsers '.$account->getUsername.' '.$account->getPassword()); 
                $runningProcesses = [];
                foreach($commands as $command){
                    try{
                        $process = new Process($command);
                        $process->start();
                        $process->setTimeout(4000);
                        $runningProcesses[] = $process;
                    }catch (\Exception $e) {
                      $this->logger->error($e);
                    }    
                }
                 //wait 
                while (count($runningProcesses)) {
                    foreach ($runningProcesses as $i => $runningProcess) {
                        // specific process is finished, so we remove it
                        if (! $runningProcess->isRunning()) {
                            unset($runningProcesses[$i]);
                        }
                        // check every second
                        sleep(1);
                    }
                }
            }
        }
    return true;
    }
}