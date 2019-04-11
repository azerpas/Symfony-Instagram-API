<?php

namespace App\Controller;

use App\Command\InstalCommand;
use Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

class AutoController extends AbstractController
{
    /**
     * @Route("/2bf6da4d-c367-40b4-93fe-dbb2194b7b94",methods={"GET"})
     */
    public function mainCommand(){
        return new Response("TTT");
    }

    /**
     * @Route("/2b")
     * @return StreamedResponse
     */
    public function getTheStuffAction(): StreamedResponse
    {
        $response = new StreamedResponse();
        $response->setCallback(function () {
            $process = new Process('php bin/console test:main');
            $process->setWorkingDirectory(getcwd());
            $process->setWorkingDirectory("../");
            $process->setTimeout(1800);
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    echo 'ERR > '.$buffer;
                    return new Response("Canno't connect to Instagram, please check your params");
                } else {
                    echo 'OUT > '.$buffer.'<br>';
                    ob_flush();
                    flush();
                }
            });
            ob_flush();
            flush();
            sleep(10);
            var_dump('Hello World');
            ob_flush();
            flush();
        });
        /*
        $response->setCallback(function () {
            var_dump('Hello World');
            ob_flush();
            flush();
            sleep(10);
            var_dump('Hello World');
            ob_flush();
            flush();
        });
        */

        return $response->send();
    }
}