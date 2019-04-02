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
class AjaxController extends AbstractController
{


     /**
    * @Route("/ajax/set_slot", name="set_slot_status", methods={"POST"},condition="request.isXmlHttpRequest()")
    */

    public function setSlotStatus(Request $req,DBRequest $bd){
     
        if ($this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['error' => 'auth required'], 401);
         }
        $value=$bd->setSlot($this->getUser(),$req->request->get('slot'),$req->request->get('value'));  
        
        return new JsonResponse(['Success'=> json_encode($value)],200);


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


    }

    /**
    * @Route("/ajax/set_bot_status", name="set_bot_status", methods={"POST"},condition="request.isXmlHttpRequest()")
    */
  
    public function setBotStatus(Request $req,DBRequest $db){
       
        $response=$db->setStatus($this->getUser(),$req->request->get('status'));
        
        if($response)
        return new JsonResponse(['output'=> "success"],200);
        else   return new JsonResponse(['output'=> "error"]);

    }

    /**
     * @Route("/ajax/search_settings", name="search_settings", methods={"POST","GET"})
     */
    public function searchSettings(Request $req, DBRequest $DBRequest){
        $search_settings = unserialize($this->getUser()->getActuelAccount()->getSearchSettings());
        if($req->isMethod("POST")){
            $keyword = $req->request->get('keyword');
            if (strpos($keyword,"@")===0){ // if contains @ then pseudo
                $keyword = str_replace("@","",$keyword); // replacing @ with nothing
                array_push($search_settings->pseudos,$keyword); // pushing current keyword into Account pseudos settings
                $search_settings = serialize($search_settings);
                $DBRequest->setSearchSettings($this->getUser(),$search_settings);
                return new JsonResponse(['output'=>'Successfully added: '.$keyword],200);
            }
            if (strpos($keyword,"#")===0){ // if contains # then hashtag
                $keyword = str_replace("#","",$keyword);
                array_push($search_settings->hashtags,$keyword);
                $search_settings = serialize($search_settings);
                $DBRequest->setSearchSettings($this->getUser(),$search_settings);
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
