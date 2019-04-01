<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use InstagramAPI\Request\Hashtag;
use App\Service\DBRequest;
use App\Service\InstaInterface;

class botCommand extends Command
{
    protected static $defaultName = 'insta:bot';

    /**
     * @var DBRequest 
     */
    protected $db;

    public function __construct(DBRequest $DBRequest, string $name = null)
    {
        $this->db = $DBRequest;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('insta:bot')
            ->setDescription('bot') 
            ->addArgument('arg', InputArgument::REQUIRED, 'arg')
        
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {    $accounts=$this->db->getAllAccounts();
        foreach($accounts as $account){
            $status=$account->getStatus();
            $slots=$account->getSlots();
            if(isTime($slots) ){}
            elseif(isTime($slots)){}  
            elseif(!isTime($slots)){}          
        }
        
       
        
    }

    /**
     * @method: check if user have defined time slots
     * if so fetching them from BD and checking if currently in time slot
     * @return: Boolean
     */
    private function isTime($slots){
        $time = new \DateTime();
        $time->format('H');
        if ($this->dbRequest->getSlots()[$time]=="on")return true;
        return false;
    }
   

    
}