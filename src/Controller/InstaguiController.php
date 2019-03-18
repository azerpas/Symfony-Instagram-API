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
use App\Repository\AccountRepository;
use Psr\Log\LoggerInterface;
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
     * @Route("/instagui/profile", name="inst_profil")
     */
    public function profilPage( Request $request,LoggerInterface $logger)
    {   $task = new SignInIg();
        $form = $this->createFormBuilder($task)
            ->add('username', TextType::class, ['label_attr' => array('class' => 'form-label'),  'attr' => [ 'class' => 'form-control' ] ])
            ->add('password', TextType::class, ['label_attr' => array('class' => 'form-label'),   'attr' => [ 'class' => 'form-control' ] ])
            ->add('connect', ButtonType::class, ['label'=> 'Test connection', 'attr' => ['onclick' => 'Connect()','class' => 'btn btn-info mt-2 ']])
            ->add('save', SubmitType::class, ['label' => 'Create Task','attr'=> [ 'class' => ' btn btn-primary mt-2' ]])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            //echo serialize($task);

            //$this->signInIg($task->getUsername(),$task->getPassword());
            // make it return a response 200 if process RUN without ERROR

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($task);
            // $entityManager->flush();

            return $this->redirectToRoute('task_success');
        }
        $usr= $this->container->get('security.token_storage')->getToken()->getUser();
       
        $logger->info($usr->getUsername());
        
        return $this->render('instagui/profile.html.twig', [
           'page'=> 'Profile', 'form'=>$form->createView()
        ]);
    }
    /**
     * @Route("/instagui/parameters", name="inst_params")
     */
    public function paramsPage(Request $request)
    {  
       
        
        return $this->render('instagui/parameters.html.twig', [
            'controller_name' => 'InstaguiController','page'=> 'paramètres'
        ]);
    }

    public function signInIg($username,$password){
        //$kernel = $this->container->get('kernel');
        $process = new Process('php bin/console instachecker '.$username.' '.$password);
        //$process->setWorkingDirectory($kernel->getProjectDir());
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'ERR > '.$buffer;
            } else {
                echo 'OUT > '.$buffer.'<br>';
            }
        });
        sleep(10);
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
    * @Route("/instagui/config_bot", name="set_config", methods={"POST"},condition="request.isXmlHttpRequest()")
    */

    public function setBotParameters(Request $req,LoggerInterface $logger){
        
       
        
        if ($this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['error' => 'auth required'], 401);
         }
        $config=$req->request->all();
        $maxFollow= $config['maxfollow']; 
        $logger->info($this->getUser()->getUsername());

     return new JsonResponse(['output'=> $config]);


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
