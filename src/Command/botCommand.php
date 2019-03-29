<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use InstagramAPI\Request\Hashtag;
use App\Service\DBRequest;

class botCommand extends Command
{
    protected static $defaultName = 'insta:bot';

    /**
     * @var DBRequest $dbRequest
     */
    protected $dbRequest;

    public function __construct(DBRequest $DBRequest, string $name = null)
    {
        $this->dbRequest = $DBRequest;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('insta:bot')
            ->setDescription('Seach intagram account') 
            ->addArgument('arg', InputArgument::REQUIRED, 'arg')
        
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = new \DateTime();
        $time->format('H');
        if ($this->dbRequest->getSlots()[$time]=="on"){

        }
       
        
    }
   

    
}