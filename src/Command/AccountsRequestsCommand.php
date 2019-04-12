<?php
/**
 * Created by PhpStorm.
 * User: Anthony
 * Date: 2019-04-08
 * Time: 12:03
 */

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AccountsRequestsCommand extends Command
{
    protected static $defaultName = 'app:user';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var \InstagramAPI\Instagram
     */
    private $ig;

    public function __construct(EntityManagerInterface $entityManager){
        $this->em = $entityManager;
        $this->ig = new \InstagramAPI\Instagram(false, false);
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Get users infos')
            ->addArgument('username', InputArgument::REQUIRED, 'My username')
            ->addArgument('password', InputArgument::REQUIRED, 'My password')
            ->addOption('followers','Get Followers')
            ->addOption('all','Get all infos')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ig->login($input->getArgument('username'),$input->getArgument('password'));
        if($input->getOption('followers')){
            $output->writeln(json_decode($this->ig->people->getSelfInfo())->user->follower_count);
            return json_decode($this->ig->people->getSelfInfo())->user->follower_count;
        }
        if($input->getOption('all')){
            $output->writeln(serialize(json_decode($this->ig->people->getSelfInfo())->user));
            return;//json_encode($this->ig->people->getSelfInfo());
        }

    }
}