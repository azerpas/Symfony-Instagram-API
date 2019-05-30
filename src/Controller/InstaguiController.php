<?php

namespace App\Controller;

use App\Entity\Account;
use App\Repository\AccountRepository;
use App\Entity\IgAccount;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class InstaguiController extends AbstractController
{
    /**
     * @Route("/instagui/home", name="inst_home")
     */
    public function homePage()
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $em = $this->getDoctrine()->getManager();
        $account = $this->getUser()->getActuelAccount() ? 1 : null;
        $acc = $this->getUser()->getActuelAccount();
        $acc->setTurnedOn("salut");
        $em->persist($acc);
        $em->flush();
        return $this->render('instagui/home.html.twig', [
            'controller_name' => 'InstaguiController','page'=> 'home','account'=>$account, 'command'=>null
        ]);
    }

    /**
     * @Route("/instagui/bots", name="inst_bots")
     */
    public function botsPage(Request $request)
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $form = $this->createFormBuilder()
            ->add('command', ChoiceType::class, [
                'choices'  => [
                    'Search by tag' => 'search:tag',
                    'Search by pseudo' => 'search:pseudo',
                    'Main Command' => 'insta:main',
                ], 'label' => 'Select your command', 'attr' => ['class'=>'m-2']
            ])
            ->add('Try command', SubmitType::class, ['label' => 'Try command','attr'=> [ 'class' => ' btn btn-primary mt-2', ($this->getUser()->getActuelAccount() ? '' :'disabled')=>'' ]])
            ->getForm();
        $form->handleRequest($request);
        //$hashtags = unserialize($this->getUser()->getActuelAccount()->getSearchSettings())->hashtags;
        if ($form->isSubmitted() && $form->isValid()) {
            $response = new StreamedResponse(); // Streamed Response allow live output
            $response->setCallback(function () use($form) {
                echo '-----------------------------------------------------------------------';
                echo '<br/><a href="history" target="_blank">Click here to open your logs</a><br/>';
                echo '-----------------------------------------------------------------------';
                ob_flush();
                flush();
                if($form->get('command')->getData() == 'insta:main'){
                    $process = new Process('php bin/console ' . $form->get('command')->getData());
                }
                else{
                    $process = new Process('php bin/console ' . $form->get('command')->getData() . ' ' . $this->getUser()->getActuelAccount()->getUsername() . ' ' . $this->getUser()->getActuelAccount()->getPassword());
                }
                $process->setWorkingDirectory(getcwd());
                $process->setWorkingDirectory("../");
                $process->setTimeout(1800);
                $process->run(function ($type, $buffer) {
                    if (Process::ERR === $type) {
                        echo 'ERR > ' . $buffer;
                        return new Response("Canno't connect to Instagram, please check your params");
                    } else {
                        echo 'OUT > ' . $buffer . '<br>';
                        ob_flush();
                        flush();
                    }
                });
            });
            return $response->send();
            //return new Response("Successfully launched process ".$form->get('command')->getData());
        }
        return $this->render('instagui/bots.html.twig', ['controller_name' => 'InstaguiController','form'=>$form->createView(),'page'=> 'bots']);
    }

    /**
     * @Route("/instagui/search", name="inst_search")
     */
    public function searchPage()
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getActuelAccount()){
            $search_settings = unserialize($this->getUser()->getActuelAccount()->getSearchSettings());
            $blacklist = $this->getUser()->getActuelAccount()->getBlacklist();
        }
        else{
            return $this->render('instagui/search.html.twig', ['controller_name' => 'InstaguiController','page'=> 'Search', 'hashtags'=>0, 'pseudos'=>0, 'blacklist'=>'']);
        }
        return $this->render('instagui/search.html.twig', [
            'controller_name' => 'InstaguiController','page'=> 'Search', 'hashtags'=>$search_settings->hashtags, 'pseudos'=>$search_settings->pseudos, 'blacklist'=>$blacklist
        ]);
    }

    /**
     * @Route("/instagui/charts", name="inst_charts")
     */
    public function chartsPage(LoggerInterface $logger, KernelInterface $kernel)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if($this->getUser()->getActuelAccount()){
            $process = new Process('php bin/console app:user ' . $this->getUser()->getActuelAccount()->getUsername() . ' ' . $this->getUser()->getActuelAccount()->getPassword(). ' --all');
            $process->setWorkingDirectory(getcwd());
            $process->setWorkingDirectory("../");
            $process->setTimeout(1800);
            $followers = '1';
            $process->run(function ($type, $buffer) use($followers) {
                if (Process::ERR === $type) {
                    echo 'ERR > ' . $buffer;
                    //return new Response("Canno't connect to Instagram, please check your params");
                } else {
                    //echo 'OUT > ' . $buffer . '<br>';
                    var_dump($buffer);
                    //return new Response($buffer);
                }
            });
            //return new Response($process->getIncrementalOutput());
            $user = unserialize($process->getIncrementalOutput());
            return $this->render('instagui/stat.html.twig', [
                'controller_name' => 'InstaguiController','page'=> 'statistiques', 'user'=>$user
            ]);
        }
        else{
            return $this->render('instagui/stat.html.twig', [
                'controller_name' => 'InstaguiController','page'=> 'statistiques', 'user'=>null
            ]);
        }
    }

    /**
     * @Route("/instagui/history", name="inst_history")
     */
    public function historyPage(){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //TODO get history likedMedias
        return $this->render('instagui/history.html.twig',[
            'controller_name' => 'InstaguiController','page'=> 'history','history'=> $this->getUser()->getActuelAccount() ? $this->getUser()->getActuelAccount()->getHistories() : null
        ]);
    }

    /**
     * @Route("/instagui/profile", name="inst_profil")
     */
    public function profilPage(Request $request,LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $usrr = $this->getUser();
        $account = new Account();
        $form = $this->createFormBuilder($account)
            ->add('username', TextType::class, ['label_attr' => array('class' => 'form-label'),  'attr' => [ 'class' => 'form-control' ] ])
            ->add('password', PasswordType::class, ['label_attr' => array('class' => 'form-label'),   'attr' => [ 'class' => 'form-control' ] ])
            ->add('connect', ButtonType::class, ['label'=> 'Test connection', 'attr' => ['onclick' => 'runTestIgAcc()','class' => 'btn btn-info mt-2 ']])
            ->add('save', SubmitType::class, ['label' => 'Add Instagram account','attr'=> [ 'class' => ' btn btn-primary mt-2' ]])
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


                
                $account->setUsername($account->getUsername());
                $account->setPassword($account->getPassword());
                $account->setSlots(json_encode(array_fill(0, 24, false)));
                $account->setSettings("{\"minfollow\":\"25\",\"maxfollow\":\"3991\",\"minfollowing\":\"25\",\"maxfollowing\":\"1992\",\"minpublication\":\"10\",\"maxpublication\":\"192\",\"picture\":\"0\",\"private\":\"1\",\"followPerHour\":\"30\",\"followPerDay\":\"116\",\"TimeToUnfollow\":\"0\",\"blackList\":\"0\",\"like\":\"2\",\"waitingTime\":\"17\",\"Type\":\"h\",\"message\":\"       type your text here ...       \"}");
                $searchSettings = new \stdClass();
                $searchSettings->pseudos = [];
                $searchSettings->hashtags = [];
                $searchSettings->blacklist = [];
                $account->setSearchSettings(serialize($searchSettings));
                $key = $account->getUsers()->count();
                $account->setUser($key,$usrr);
                $em = $this->getDoctrine()->getManager();
                $em->persist($account);
                $em->flush();
                // */
            }
            else{
                // else the result become the account instance
                $account = $result;
            }


           
            $key = $usrr->getAccounts()->count();
            $usrr->setAccount($key,$account);
            $entityManager = $this->getDoctrine()->getManager();
            $accounts=$usrr->getAccounts();
            $usrr->setActuelAccount($accounts->get(0));
            $entityManager->persist($usrr);
            $entityManager->flush();
            //*/

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
        //$logger->info($usrr->getActuelAccount()->getProxy());
        // -------------- /TEST/ -------------- //
        return $this->render('instagui/profile.html.twig', [
           'page'=> 'Profile', 'form'=>$form->createView(), 'user'=>$this->getUser(), 'accounts'=>$accs, 'proxy'=> ($usrr->getActuelAccount() ? $usrr->getActuelAccount()->getProxy() : null)
        ]);
    }
    /**
     * @Route("/instagui/parameters", name="inst_params")
     */
    public function paramsPage(Request $request)
    {   //check for login user redirect if null
        if($this->getUser()->getActuelAccount() == null){
            $param = null;
        }
        else{
            $param=json_decode($this->getUser()->getActuelAccount()->getSettings());
        }
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('instagui/parameters.html.twig', [
            'page'=> 'paramètres','param'=>  $param
        ]);
    }

    /**
     * @Route("/instagui/scheduling", name="inst_scheduling")
     */
    public function schedulingPage(LoggerInterface $logger)
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
    public function nextAccount (LoggerInterface $logger){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $actuelAccount=$user->getActuelAccount();
        $accounts=$user->getAccounts();
        $index=$accounts->indexOf($actuelAccount);
        if($index==$accounts->count()-1)$index=0;
        else $index++;
        $user->setActuelAccount($accounts->get($index)); 
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('inst_home');
    }
     /**
     * @Route("/instagui/previousAccount",name="previousAccount")
     */
    public function previousAccount (LoggerInterface $logger){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $actuelAccount=$user->getActuelAccount();
        $accounts=$user->getAccounts();
        $index=$accounts->indexOf($actuelAccount);
        if($index==0)$index=$accounts->count();
        $index=$index-1;
        $user->setActuelAccount($accounts->get($index)); 
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('inst_home');
    }

    /**
     * @Route("/instagui/lists",name="inst_lists")
     */
    public function lists(LoggerInterface $log){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if($this->getUser()->getActuelAccount()){
            return $this->render('instagui/lists.html.twig',[
                'controller_name' => 'InstaguiController','page'=> 'lists','peoples'=> $this->getDoctrine()->getManager()->getRepository('App\Entity\People')->findPeopleToFollowTrueByAccount($this->getUser()->getActuelAccount())
            ]);
        }else{
            return $this->render('instagui/lists.html.twig',['controller_name' => 'InstaguiController','page'=> 'lists','peoples'=> null]);
        }
    }
}
