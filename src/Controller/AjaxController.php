<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Service\DBRequest;
use App\Entity\History;
class AjaxController extends AbstractController
{


    /**
     * Setting a slot status (true or false)
     * @Route("/ajax/set_slot", name="set_slot_status", methods={"POST"},condition="request.isXmlHttpRequest()")
     */

    public function setSlotStatus(Request $req){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $em = $this->getDoctrine()->getManager();

        $account = $this->getUser()->getActuelAccount();
        if($account==null){
            return new JsonResponse(['ERROR'=> 'No current account found'],419);
        }
        $slot = $req->request->get('slot'); // fetching slot given by user
        $slots = json_decode($account->getSlots()); // getting accounts slots
        if($req->request->get('value')=="off") {
            $slots[$slot] = false;
        }
        else{
            $slots[$slot]=true;
        }
        $account->setSlots(json_encode($slots));
        $em->persist($account);
        $em->flush();
        return new JsonResponse(['Success'=> json_encode($req->request->get('value'))],200);

    }

    /**
    * @Route("/ajax/edit_profile", name="edit_profile", methods={"POST"},condition="request.isXmlHttpRequest()")
    */

    public function editProfile(Request $req,DBRequest $bd,UserPasswordEncoderInterface $passwordEncoder){
     
        if ($this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['error' => 'auth required'], 401);
         }
        if($req->request->get('pwdConfirm')!== $req->request->get('pwd')) 
            return new JsonResponse(['error'=> ""],200);
        if(strlen($req->request->get('pwd'))!=0)    
         $password = $passwordEncoder->encodePassword($this->getUser(),$req->request->get('pwd'));
        $value=$bd->editProfile($this->getUser(),$password,$req->request->get('email'));  
        
        return new JsonResponse(['Success'=> "profile"],200);


    }

     /**
    * @Route("/ajax/config_bot", name="set_config", methods={"POST"},condition="request.isXmlHttpRequest()")
    */

    public function setBotParameters(Request $req,LoggerInterface $logger,DBRequest $service){

        if ($this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['error' => 'auth required'], 401);
        }
        $logger->info($this->getUser()->getUsername());
        $value=$service->setParams($this->getUser(),$req->request->all()); 
        return new JsonResponse(['output'=> $value]);
        /* REPLACING DBRequest::setParams
        $account = $this->getUser()->getActuelAccount();
        if($account==null){
            return new JsonResponse(['output'=>'no Instagram account asigned for this account'], 419);
        }
        $account->setSettings(json_encode($req->request->all()));
        $this->em->persist($account);
        $this->em->flush();
        return new JsonResponse(['output'=> 'success'],200);;
         */


    }

    /**
    * @Route("/ajax/set_bot_status", name="set_bot_status", methods={"POST"},condition="request.isXmlHttpRequest()")
    */
  
    public function setBotStatus(Request $req,DBRequest $db){
        /*
        $response=$db->setStatus($this->getUser(),$req->request->get('status'));

        if($response)
        return new JsonResponse(['output'=> "success"],200);
        else   return new JsonResponse(['output'=> "error"]);
        */
        $em = $this->getDoctrine()->getManager();
        $account = $this->getUser()->getActuelAccount();
        if($account==null) {
            return new JsonResponse(['output'=> "no Instagram account assigned for this user "], 419);
        }
        if($req->request->get('status') == "true") {
            $account->setStatus(true);
        }
        else {
            $account->setStatus(false);
        }
        $em->persist($account);
        $em->flush();
        return new JsonResponse(['output'=> "Success"],200);

    }

    /**
     * @Route("/ajax/search_settings", name="search_settings", methods={"POST","GET"})
     */
    public function searchSettings(Request $req, DBRequest $DBRequest){
        $search_settings = unserialize($this->getUser()->getActuelAccount()->getSearchSettings());
        $em = $this->getDoctrine()->getManager();
        if($req->isMethod("POST")){
            $keyword = $req->request->get('keyword');
            if (strpos($keyword,"@")===0){ // if contains @ then pseudo
                $keyword = str_replace("@","",$keyword); // replacing @ with nothing
                array_push($search_settings->pseudos,$keyword); // pushing current keyword into Account pseudos settings
                $search_settings = serialize($search_settings);
                //$DBRequest->setSearchSettings($this->getUser(),$search_settings);
                // REPLACING DBRequest::setSearchSettings
                $account = $this->getUser()->getActuelAccount();
                $account->setSearchSettings($search_settings);
                $em->persist($account);
                $em->flush();
                $history = new History();
                $history->setType('searchSet');
                $history->setMessage('Added @'.$keyword.' as a keyword !');
                $history->setFromAccount($account);
                $em->persist($history);
                $em->flush();
                return new JsonResponse(['output'=>'Successfully added: '.$keyword],200);
            }
            if (strpos($keyword,"#")===0){ // if contains # then hashtag
                $keyword = str_replace("#","",$keyword);
                array_push($search_settings->hashtags,$keyword);
                $search_settings = serialize($search_settings);
                $DBRequest->setSearchSettings($this->getUser(),$search_settings);
                // REPLACING DBRequest::setSearchSettings
                $account = $this->getUser()->getActuelAccount();
                $account->setSearchSettings($search_settings);
                $em->persist($account);
                $em->flush();
                $history = new History();
                $history->setType('searchSet');
                $history->setMessage('Added #'.$keyword.' as a keyword !');
                $history->setFromAccount($account);
                $em->persist($history);
                $em->flush();
                return new JsonResponse(['output'=>'Successfully added: '.$keyword],200);
            }
            return new JsonResponse(['output'=>'Could not read the keyword: '.$keyword],400);
        }
        elseif($req->isMethod("GET")){
            return new JsonResponse(['method'=>'GET','output'=> serialize($search_settings)],200);
        }
        return new JsonResponse(['method'=>'No declared method','output'=> serialize($search_settings)],400);
    }


   

}
