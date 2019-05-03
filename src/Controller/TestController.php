<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\People;
use App\Repository\AccountRepository;
use App\Entity\IgAccount;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TestController extends AbstractController
{

    public function sign(Request $request)
    {
        $task = new Task();
        $task->setTask('Form for instagram');
        $form = $this->createFormBuilder($task)
            ->add('task', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Task'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();
            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($task);
            // $entityManager->flush();
            return $this->redirectToRoute('task_success');
        }
        return $this->render('task/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/instagui/testIgAccount", name="test_ig_account", methods={"POST"},condition="request.isXmlHttpRequest()")
     */
    public function testIgAccount(Request $req)
    {
        $username = $req->request->get('username');
        $password = $req->request->get('password');
        try {
            $process = new Process('php bin/console insta:instance ' . $username . ' ' . $password);
            $process->setWorkingDirectory(getcwd());
            $process->setWorkingDirectory("../");
            $process->start();
            $process->wait();
            if ($process->isSuccessful()) {
                return new JsonResponse(["output" => "Successfully connected to " . $username], 200);
            } else {
                return new JsonResponse(["output" => "Please check password/username"], 400);
            }
        } catch (\Exception $e) {
            return new JsonResponse(["output" => "Error processing"], 403);
        }
    }

    /**
     * @Route("/instagui/testProxy", name="test_ig_proxy", methods={"POST"},condition="request.isXmlHttpRequest()")
     */
    public function testProxy(Request $req){
        $account = $this->getUser()->getActuelAccount();
        $proxy = $req->request->get('proxy');
        try {
            $process = new Process('php bin/console insta:instance ' . $account->getUsername() . ' ' . $account->getPassword(). ' --proxy='.$proxy);
            $process->setWorkingDirectory(getcwd());
            $process->setWorkingDirectory("../");
            $process->start();
            $process->wait();
            if ($process->isSuccessful()) {
                return new JsonResponse(["output" => "Successfully connected to " . $account->getUsername()], 200);
            } else {
                return new JsonResponse(["output" => "Please check password/username or proxy"], 400);
            }
        } catch (\Exception $e) {
            return new JsonResponse(["output" => "Error processing"], 403);
        }
    }

    /**
     * @Route("/findPeople")
     */
    public function findPeopleDB(LoggerInterface $logger){
        $logger->info('Starting insert to DB');
        $account = $this->getDoctrine()
            ->getRepository(People::class)
            ->findOneByUsername('driftersmd',$this->getUser()->getActuelAccount());
        $logger->info($account->getInstaId());
        return new Response($account->getInstaId());
    }

    /**
     * @Route("/1a")
     */
    public function getUserInfos(LoggerInterface $logger){
        $response = new StreamedResponse(); // Streamed Response allow live output
        $followers = '';
        $response->setCallback(function () use($followers){
            echo '-----------------------------------------------------------------------';
            echo '<br/><a href="history" target="_blank">Click here to open your logs</a><br/>';
            echo '-----------------------------------------------------------------------';
            ob_flush();
            flush();
            $process = new Process('php bin/console app:user ' . $this->getUser()->getActuelAccount()->getUsername() . ' ' . $this->getUser()->getActuelAccount()->getPassword(). ' --all');
            //$process->setWorkingDirectory(getcwd());
            $process->setWorkingDirectory("../");
            $process->setTimeout(1800);
            $process->run(function ($type, $buffer) use($followers) {
                if (Process::ERR === $type) {
                    echo 'ERR > ' . $buffer;
                    return new Response("Canno't connect to Instagram, please contact admin");
                } else {
                    echo 'OUT > ' . $buffer . '<br>';
                    ob_flush();
                    flush();
                }
            });
        });
        $followers = $response->send();
        return new Response('test :::: '.$followers);
    }
}