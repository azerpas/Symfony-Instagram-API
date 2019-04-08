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
use App\Entity\History;
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
            ->setDescription('launch other commands in parallel')    
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
        // SET TIMEZONE !!!
        // CURRENTLY -2 HOUR
        $time = $time->format('H');
        if (strpos($time,"0")===0) { // if 0 before hour it will glitch
            $time = str_replace("0", "", $time);
        }
        return $slots[$time];
    }

   
    protected function execute(InputInterface $input, OutputInterface $output){
        //accounts list 
        $output->writeln("<info>#get accounts list</info>");       
        $accounts=$this->entityManager->getRepository('App\Entity\Account')->findAll();
       
       
        
        //process array
        $runningProcesses = [];

        foreach($accounts as $account){
            $output->writeln($account->getUsername().' '.$this->isTime($account).' '.$account->getStatus());
            if($account->getStatus() && $this->isTime($account)){
                $output->writeln("Account: ".$account->getUsername()." will be activated...");

                //command list
                $commands=array();
                array_push($commands,'php bin/console insta:instance '.$account->getUsername().' '.$account->getPassword());
                // TODO : if current database people is > 40 then don't use
                $numberOfPeople = count($this->entityManager->getRepository('App\Entity\People')->findAllByAccount($account));
                if($numberOfPeople<40){
                    $output->writeln("Currently more than 40 people in database, not searching...");
                    array_push($commands,'php bin/console search:tag '.$account->getUsername().' '.$account->getPassword());
                }
                array_push($commands,'php bin/console app:likeAndFollowUsers '.$account->getUsername().' '.$account->getPassword());
                array_push($commands,'php bin/console insta:contact '.$account->getUsername().' '.$account->getPassword());
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
        //TODO need to add something like: is the bot still running -> history
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
            $output->writeln("");    
        $output->writeln("<info>#successful execution...</info>");  
    return true;
    }
}
}