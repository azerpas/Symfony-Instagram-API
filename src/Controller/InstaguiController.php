<?php

namespace App\Controller;

use App\Entity\IgAccount;
use App\Entity\Task;
use App\Service\DBRequest;
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
use App\Repository\AccountRepository;
use Psr\Log\LoggerInterface;

class InstaguiController extends AbstractController
{
    /**
     * @Route("/instagui/home", name="inst_home")
     */
    public function homePage()
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('instagui/home.html.twig', [
            'controller_name' => 'InstaguiController','page'=> 'home'
        ]);
    }
    
    /**
     * @Route("/instagui/bots", name="inst_bots")
     */
    public function botsPage()
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('instagui/bots.html.twig', ['controller_name' => 'InstaguiController','page'=> 'bots']);
    }

    /**
     * @Route("/instagui/charts", name="inst_charts")
     */
    public function chartsPage()
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('instagui/stat.html.twig', [
            'controller_name' => 'InstaguiController','page'=> 'statistiques'
        ]);
    }
    /**
     * @Route("/instagui/profile", name="inst_profil")
     */
    public function profilPage(Request $request,LoggerInterface $logger,DBRequest $DBRequest)
    {  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $usrr = $this->getUser();
        $ig = new IgAccount();
        $form = $this->createFormBuilder($ig)
            ->add('username', TextType::class, ['label_attr' => array('class' => 'form-label'),  'attr' => [ 'class' => 'form-control' ] ])
            ->add('password', TextType::class, ['label_attr' => array('class' => 'form-label'),   'attr' => [ 'class' => 'form-control' ] ])
            ->add('connect', ButtonType::class, ['label'=> 'Test connection', 'attr' => ['onclick' => 'runTestIgAcc()','class' => 'btn btn-info mt-2 ']])
            ->add('save', SubmitType::class, ['label' => 'Create Task','attr'=> [ 'class' => ' btn btn-primary mt-2' ]])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ig = $form->getData();
            // Insert into database the Instagram Account into usrr "accounts" column using DBRequest service.
            $DBRequest->assignInstagramAccount($usrr->getUsername(),$ig->getUsername(),$ig->getPassword());
            return $this->redirectToRoute('task_success');
        }
        $usr= $this->container->get('security.token_storage')->getToken()->getUser();
        $logger->info($usr->getUsername());
        $logger->info($usrr->getUsername());
        
        return $this->render('instagui/profile.html.twig', [
           'page'=> 'Profile', 'form'=>$form->createView()
        ]);
    }
    /**
     * @Route("/instagui/parameters", name="inst_params")
     */
    public function paramsPage(Request $request)
    {   //check for login user redirect if null
        
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');  
        
        return $this->render('instagui/parameters.html.twig', [
            'controller_name' => 'InstaguiController','page'=> 'paramètres'
        ]);
    }

    /**
     * @Route("/instagui/signInTest")
     * @return Response
     */
    public function signInIg(){
        //$kernel = $this->container->get('kernel');
        $process = new Process('php bin/console insta:instance alexis ruffier');
        $process->setWorkingDirectory(getcwd());
        $process->setWorkingDirectory("../");
        //$process->setWorkingDirectory($kernel->getProjectDir());
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'ERR > '.$buffer;
                return new Response("Canno't connect to Instagram, please check your params");
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
    * @Route("/instagui/config_bot", name="set_config", methods={"POST"},condition="request.isXmlHttpRequest()")
    */

    public function setBotParameters(Request $req,LoggerInterface $logger,DBRequest $service){

        if ($this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['error' => 'auth required'], 401);
         }
        $logger->info($this->getUser()->getUsername());
        $value=$service->setParams($this->getUser(),$req->request->all()); 
     return new JsonResponse(['output'=> $value]);


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

    /**
     * @Route("/instagui/testIgAccount", name="test_ig_account", methods={"POST"},condition="request.isXmlHttpRequest()")
     */
    public function testIgAccount(Request $req){
        $username = $req->request->get('username');
        $password = $req->request->get('password');
        try{
            $process = new Process('php bin/console insta:instance '.$username.' '.$password);
            $process->setWorkingDirectory(getcwd());
            $process->setWorkingDirectory("../");
            $process->start();
            $process->wait();
            if($process->isSuccessful()){
                return new JsonResponse(["output" => "Successfully connected to ".$username],200);
            }
            else{
                return new JsonResponse(["output" => "Please check password/username"],400);
            }
        }catch (\Exception $e) {
            return new JsonResponse(["output" => "Error processing"],403);
        }
    }

}
