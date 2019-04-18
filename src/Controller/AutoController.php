<?php

namespace App\Controller;

use App\Command\InstalCommand;
use Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

class AutoController extends AbstractController
{
    /**
     * @Route("/2bf6da4d-c367-40b4-93fe-dbb2194b7b94",name="mainAuto",methods={"GET"})
     */
    public function mainCommand()
    {
        try{
            $request = Request::createFromGlobals();
            $key = $request->headers->get('apikey');
            if(trim($key) != "aaaa"){
                return new JsonResponse(['output'=>'Not allowed'],404);
            }
        }catch (\Exception $e){
            return new JsonResponse(['output'=>'Not allowed'],400);
        }
        $response = new StreamedResponse(); // Streamed Response allow live output
        $response->setCallback(function () {
            echo '-----------------------------------------------------------------------';
            echo '<br/><a href="history" target="_blank">Click here to open your logs</a><br/>';
            echo '-----------------------------------------------------------------------';
            ob_flush();
            flush();
            $process = new Process('php bin/console insta:main');
            $process->setWorkingDirectory(getcwd());
            $process->setWorkingDirectory("../");
            $process->setTimeout(1800);
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    echo 'ERR > ' . $buffer;
                    return new Response("Canno't connect to Instagram, please contact admin");
                } else {
                    echo 'OUT > ' . $buffer . '<br>';
                    // these two next lines will stream in real time the output of the process
                    ob_flush();
                    flush();
                }
            });
        });
        return $response->send();
    }


    /**
     * @Route("/2a")
     * @return StreamedResponse
     */
    public function test(){
        $response = new StreamedResponse(); // Streamed Response allow live output
        $response->setCallback(function (){
            for ($i=0;$i<10;$i++){
                echo '{"output":"loading"}';
                ob_flush();
                flush();
                sleep(2);
            }
        });
        return $response->send();
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