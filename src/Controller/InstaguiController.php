<?php

namespace App\Controller;

use App\Entity\SignInIg;
use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class InstaguiController extends AbstractController
{
    /**
     * @Route("/instagui/home", name="inst_home")
     */
    public function homePage()
    {
        return $this->render('instagui/home.html.twig', [
            'controller_name' => 'InstaguiController','page'=> 'home'
        ]);
    }
    
    /**
     * @Route("/instagui/bots", name="inst_bots")
     */
    public function botsPage()
    {
        return $this->render('instagui/bots.html.twig', ['controller_name' => 'InstaguiController','page'=> 'bots']);
    }

    /**
     * @Route("/instagui/charts", name="inst_charts")
     */
    public function chartsPage()
    {
        return $this->render('instagui/stat.html.twig', [
            'controller_name' => 'InstaguiController','page'=> 'statistiques'
        ]);
    }
    /**
     * @Route("/instagui/parameters", name="inst_params")
     */
    public function paramsPage(Request $request)
    {
        $task = new SignInIg();
        $form = $this->createFormBuilder($task)
            ->add('username', TextType::class)
            ->add('password', TextType::class)
            ->add('connect', ButtonType::class, ['label'=> 'Test connection', 'attr' => ['onclick' => 'Connect()']])
            ->add('save', SubmitType::class, ['label' => 'Create Task'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            //echo serialize($task);

            $this->signInIg($task->getUsername(),$task->getPassword());
            // make it return a response 200 if process RUN without ERROR

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($task);
            // $entityManager->flush();

            return $this->redirectToRoute('task_success');
        }
        return $this->render('instagui/params.html.twig', [
            'controller_name' => 'InstaguiController','page'=> 'paramètres', 'form'=>$form->createView()
        ]);
    }

    public function signInIg($username,$password){
        $kernel = $this->container->get('kernel');
        $process = new Process('php bin/console instachecker '.$username.' '.$password);
        $process->setWorkingDirectory($kernel->getProjectDir());
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'ERR > '.$buffer;
            } else {
                echo 'OUT > '.$buffer.'<br>';
            }
        });
        return new Response("Successfully launched process");
    }

    /**
     * @Route("/instagui/taskSucess",name="task_success")
     */
    public function taskSucess(){
        return new Response("Successfully received form Data");
    }

    public function sign(Request $request){
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
    * @Route("/instagui/set_search_bot", name="set_search_bot", methods={"POST"},condition="request.isXmlHttpRequest()")
    */

public function setBotParameters(Request $req){
       
    $tags=$req->request->get('white_list_tags');
    
return new JsonResponse(['output'=> $tags]);


}

/**
* @Route("/instagui/set_bot_status", name="set_bot_status", methods={"POST"},condition="request.isXmlHttpRequest()")
*/

public function setBotStatus(Request $req){
   
    $bot=$req->request->get('bot');
    $value=$req->request->get('value');
   
        $message=$bot." bot turned ".$value;
return new JsonResponse(['output'=> $message]);


}
}
