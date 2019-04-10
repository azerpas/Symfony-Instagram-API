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
            $process->setTimeout(1800);
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
     * @Route("/instagui/charts", name="inst_charts")
     */
    public function chartsPage(LoggerInterface $logger)
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('instagui/stat.html.twig', [
            'controller_name' => 'InstaguiController','page'=> 'statistiques', 'followerCount'=>''
        ]);
    }

    /**
     * @Route("/instagui/history", name="inst_history")
     */
    public function historyPage(){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //TODO get history likedMedias
        return $this->render('instagui/history.html.twig',[
            'controller_name' => 'InstaguiController','page'=> 'history','history'=>$this->getUser()->getActuelAccount()->getHistories()
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


                /* REPLACING DBrequest::createInstagramAccount
                $account->setUsername($account->getUsername());
                $account->setPassword($account->getPassword());
                $account->setSlots(json_encode(array_fill(0, 24, false)));
                $account->setUser(0,$user); // here
                $searchSettings = new stdClass();
                $searchSettings->pseudos = [];
                $searchSettings->hashtags = [];
                $searchSettings->blacklist = [];
                $account->setSearchSettings(serialize($searchSettings));
                $key = $account->getUsers()->count();
                $account->setUser($key,$user);
                $em = $this->getDoctrine()->getManager();
                $em->persist($account);
                $em->flush();
                 */
                $DBRequest->createInstagramAccount($usrr,$account);
            }
            else{
                // else the result become the account instance
                $account = $result;
            }


            // Insert into database the Instagram Account into usrr "accounts" column using DBRequest service.

            /* REPLACING DBrequest::assignInstagramAccount
            // $key = $usrr->getAccounts()->count();
            // $user->setAccount($key,$account);
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($user);
            // $entityManager->flush();
            */
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
        $param=json_decode($this->getUser()->getActuelAccount()->getSettings());
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('instagui/parameters.html.twig', [
            'page'=> 'paramètres','param'=>  $param
        ]);
    }

    /**
     * @Route("/instagui/scheduling", name="inst_scheduling")
     */
    public function schedulingPage(DBRequest $DBRequest,LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $slots=json_decode($this->getUser()->getActuelAccount()->getSlots());
        $status=$this->getUser()->getActuelAccount()->getStatus();
        return $this->render('instagui/scheduling.html.twig', [ 'page'=> 'scheduling','slots' =>$slots,'status'=>$status]);
    }

    /**
     * @Route("/instagui/taskSucess",name="task_success")
     */
    public function taskSucess(){
        return new Response("Successfully received form Data");
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
