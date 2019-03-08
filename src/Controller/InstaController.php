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

class InstaController extends AbstractController
{
    /**
     * @Route("/insta", name="insta", methods="GET")
     */
    public function index(Request $request, KernelInterface $kernel)
    {
        /////// CONFIG ///////
        $username       = '';
        $password       = '';
        $debug          = true;
        $truncatedDebug = false;
        //////////////////////
        echo ($request->get('type'));
        return new Response("INDEX TEST");

    }

    private function runInstCheck($kernel) {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command'         => InstalCommand::getDefaultName(),
            // (optional) define the value of command arguments
            'username'        => 'vetixy',
            'password'        => 'mdp=VyIm18',
            // (optional) pass options to the command
            //'--message-limit' => $messages,
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();

        // return new Response(""), if you used NullOutput()
        return new Response($content);
    }
}
