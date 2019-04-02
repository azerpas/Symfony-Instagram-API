<?php

namespace App\Controller;

use App\Entity\Account;
use App\Repository\AccountRepository;
use App\Entity\IgAccount;
use App\Entity\Task;
use App\Entity\User;
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
     * @Route("/instagui/search", name="inst_search")
     */
    public function searchPage()
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $search_settings = unserialize($this->getUser()->getActuelAccount()->getSearchSettings());
        return $this->render('instagui/search.html.twig', [
            'controller_name' => 'InstaguiController','page'=> 'Search', 'hashtags'=>$search_settings->hashtags, 'pseudos'=>$search_settings->pseudos, 'blacklist'=>''
        ]);
    }

     /**
     * @Route("/instagui/scheduling", name="inst_scheduling")
     */
    public function schedulingPage(DBRequest $DBRequest,LoggerInterface $logger)
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $slots=$DBRequest->getSlots($this->getUser(),$logger);
        $status=$DBRequest->getStatus($this->getUser());
        return $this->render('instagui/scheduling.html.twig', [ 'page'=> 'scheduling','slots' =>$slots,'status'=>$status]);
    }

    /**
     * @Route("/instagui/bots", name="inst_bots")
     */
    public function botsPage(Request $request)
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $form = $this->createFormBuilder()
            ->add('Try search command', SubmitType::class, ['label' => 'Try search command','attr'=> [ 'class' => ' btn btn-primary mt-2' ]])
            ->getForm();
        $form->handleRequest($request);
        //$hashtags = unserialize($this->getUser()->getActuelAccount()->getSearchSettings())->hashtags;
        if ($form->isSubmitted() && $form->isValid()) {
            $process = new Process('php bin/console search:tag '.$this->getUser()->getActuelAccount()->getUsername().' '.$this->getUser()->getActuelAccount()->getPassword());
            $process->setWorkingDirectory(getcwd());
            $process->setWorkingDirectory("../");
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
        return $this->render('instagui/bots.html.twig', ['controller_name' => 'InstaguiController','form'=>$form->createView(),'page'=> 'bots']);
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
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $usrr = $this->getUser();
        $account = new Account();
        $form = $this->createFormBuilder($account)
            ->add('username', TextType::class, ['label_attr' => array('class' => 'form-label'),  'attr' => [ 'class' => 'form-control' ] ])
            ->add('password', PasswordType::class, ['label_attr' => array('class' => 'form-label'),   'attr' => [ 'class' => 'form-control' ] ])
            ->add('connect', ButtonType::class, ['label'=> 'Test connection', 'attr' => ['onclick' => 'runTestIgAcc()','class' => 'btn btn-info mt-2 ']])
            ->add('save', SubmitType::class, ['label' => 'Create Task','attr'=> [ 'class' => ' btn btn-primary mt-2' ]])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $account = $form->getData(); // we fetch the input

            // We check if the account (username & password) given by the
            // user is contained in the table Account
            $result = $this->getDoctrine()
                ->getRepository(Account::class)
                ->selectAccount($account->getUsername());
                // check this method into /src/Repository/AccountRepository.php
            if($result == null){
                // if NOT, then we create the account and submit it to the BD
                $DBRequest->createInstagramAccount($usrr,$account);
            }
            else{
                // else the result become the account instance
                $account = $result;
            }


            // Insert into database the Instagram Account into usrr "accounts" column using DBRequest service.
            $DBRequest->assignInstagramAccount($usrr,$account,$account->getUsername(),$account->getPassword(),$logger);

            return $this->redirectToRoute('inst_profil');
        }

        // -------------- TEST -------------- //
        $logger->info($usrr->getUsername());
        $logger->info(serialize($usrr->getAccounts()));
        if($usrr->getAccount(0) != null){ // test if user has accounts
            $logger->info($usrr->getAccount(0)->getUsername());
            $accs = $usrr->getAccounts();
        }
        else{ // else it returns null (for .twig)
            $accs = null;
        }
        // -------------- /TEST/ -------------- //
        return $this->render('instagui/profile.html.twig', [
           'page'=> 'Profile', 'form'=>$form->createView(), 'user'=>$this->getUser(), 'accounts'=>$accs
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
    /**
     * @Route("/findAccount")
     */
    public function testAccountTableDB(DBRequest $service,LoggerInterface $logger){
        $logger->info('Starting insert to DB');
        $account = $this->getDoctrine()
            ->getRepository(Account::class)
            ->selectAccount($this->getUser(),'testAccount','testPassword');
        $service->assignInstagramAccount($this->getUser(),$account,'testAccount','testPassword');
        $logger->info('went well');
        return new Response('test');
    }
    /**
     * @Route("/instagui/nextAccount",name="nextAccount")
     */
    public function nextAccount (DBRequest $db,LoggerInterface $logger){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 
        $db->getNextAccount($this->getUser());
        return $this->redirectToRoute('inst_home');
    }
     /**
     * @Route("/instagui/previousAccount",name="previousAccount")
     */
    public function previousAccount (DBRequest $db,LoggerInterface $logger){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 
        $db->getPreviousAccount($this->getUser());
        return $this->redirectToRoute('inst_home');
    }
}
