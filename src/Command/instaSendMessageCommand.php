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

class instaSendMessageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('insta:message')
            ->setDescription('Send message to intagram account')
            ->addArgument('username',InputArgument::REQUIRED,'username?') 
            ->addArgument('password',InputArgument::REQUIRED,'password?')  
            ->addArgument('message',InputArgument::REQUIRED,'message?') 
            ->addArgument('users',InputArgument::IS_ARRAY,'user name?')
            ->addOption('only', null, InputOption::VALUE_REQUIRED);
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   $io = new SymfonyStyle($input, $output);
        $command = $this->getApplication()->find('insta:instance');
        $arguments = [
            'command' => 'insta:instance',
            'username'    => $input->getArgument('username') ,
            'password' => $input->getArgument('password') ,
           
        ];
        echo $input->getArgument('username');
        echo $input->getArgument('password');
        $greetInput = new ArrayInput($arguments);
        $ig = $command->run($greetInput, $output);
        
        $only = $input->getOption('only');
       foreach( $input->getArgument('users') as $user)
        { echo $user;
             
         
        }   
         
        $ig->direct_message($input->getArgument('users'), $input->getArgument('message'));



    }

   

    
}