<?php

// src/Command/LikeAndFollowUsersCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\DBRequest;
use App\Repository\UserRepository;

class TestCommand extends Command
{
    protected static $defaultName = 'test:main';

    /**
     * @var DBRequest
     */
    private $db;

    public function __construct(DBrequest $dbRequest){
        $this->db = $dbRequest;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Testing main command')
            ->addArgument('username', InputArgument::REQUIRED, 'My username')
        ;
    }

    public function isTime($slots, OutputInterface $output){
        if($slots == null){ //|| $slots == {}){
            $output->writeln("No slots");
        }
        else{
            $dt = new \DateTime();
            // need timezone change
            $dt = $dt->format("H");
            $output->writeln($dt);
            $slots = json_decode($slots);
            for($i = 0; $i < count($slots) ; $i++){
                // if this slot is set to true
                // AND
                // time equal current time -> return true
                if($slots[$i] == true && $i == intval($dt)){
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /////// CONFIG ///////
        $debug          = false;
        $truncatedDebug = false;
        //////////////////////
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
        try {
        }
        catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }
    }
}