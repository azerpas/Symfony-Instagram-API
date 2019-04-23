<?php

namespace App\Controller;

use Fbns\Client\Json;
use function PHPSTORM_META\type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\History;
use App\Entity\Account;
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

    public function editProfile(Request $req,UserPasswordEncoderInterface $passwordEncoder){
     
        if ($this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['error' => 'auth required'], 401);
         }
        $em = $this->getDoctrine()->getManager();
        if($req->request->get('pwdConfirm')!== $req->request->get('pwd')) 
            return new JsonResponse(['error'=> "password and confirm password should be same"],401);
        if(strlen($req->request->get('pwd'))!=0)    
        {$password = $passwordEncoder->encodePassword($this->getUser(),$req->request->get('pwd'));  
            $this->getUser()->setPassword($password);
        }
        if(strlen($req->request->get('email'))!=0)$this->getUser()->setEmail($req->request->get('email'));
        if(strlen($password)!=0)
        
        $em->persist($this->getUser());
        $em->flush();
        return new JsonResponse(['Success'=> "profile"],200);


    }

     /**
    * @Route("/ajax/config_bot", name="set_config", methods={"POST"},condition="request.isXmlHttpRequest()")
    */

    public function setBotParameters(Request $req){
 
        if ($this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['error' => 'auth required'], 401);
        }
        $em = $this->getDoctrine()->getManager();
        $account=$this->getUser()->getActuelAccount();
        if($account==null){
             return new JsonResponse(array('message' => 'no Instagram account asigned for this account '), 419);
        }
        $account->setSettings(json_encode($req->request->all()));
        $em->persist($account);
        $em->flush();
        return  new JsonResponse(array('message' => 'success'), 200);


    }

    /**
    * @Route("/ajax/set_bot_status", name="set_bot_status", methods={"POST"},condition="request.isXmlHttpRequest()")
    */
  
    public function setBotStatus(Request $req){
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
     * @Route("/ajax/search_settings", name="search_settings", methods={"POST","GET","DELETE"})
     */
    public function searchSettings(Request $req){
        $search_settings = unserialize($this->getUser()->getActuelAccount()->getSearchSettings());
        $em = $this->getDoctrine()->getManager();
        if($req->isMethod("POST")){
            $keyword = $req->request->get('keyword');
            if (strpos($keyword,"@")===0){ // if contains @ then pseudo
                $keyword = str_replace("@","",$keyword); // replacing @ with nothing
                array_push($search_settings->pseudos,$keyword); // pushing current keyword into Account pseudos settings
                $search_settings = serialize($search_settings);
                $account = $this->getUser()->getActuelAccount();
                $account->setSearchSettings($search_settings);
                $em->persist($account);
                $em->flush();
                $history = new History();
                $history->setType('searchSet');
                $history->setMessage('Added @'.$keyword.' as a keyword !');
                $history->setFromAccount($account);
                $history->setDate(new \DateTime());
                $em->persist($history);
                $em->flush();
                return new JsonResponse(['output'=>'Successfully added: '.$keyword],200);
            }
            if (strpos($keyword,"#")===0){ // if contains # then hashtag
                $keyword = str_replace("#","",$keyword);
                array_push($search_settings->hashtags,$keyword);
                $search_settings = serialize($search_settings);
                $account = $this->getUser()->getActuelAccount();
                $account->setSearchSettings($search_settings);
                $em->persist($account);
                $em->flush();
                $history = new History();
                $history->setType('searchSet');
                $history->setMessage('Added #'.$keyword.' as a keyword !');
                $history->setFromAccount($account);
                $history->setDate(new \DateTime());
                $em->persist($history);
                $em->flush();
                return new JsonResponse(['output'=>'Successfully added: '.$keyword],200);
            }
            return new JsonResponse(['output'=>'Could not read the keyword: '.$keyword],400);
        }
        elseif($req->isMethod("GET")){
            return new JsonResponse(['method'=>'GET','output'=> serialize($search_settings)],200);
        }
        elseif($req->isMethod("DELETE")){
            $keyword = $req->request->get('keyword');
            if (strpos($keyword,"@")===0) { // if contains @ then pseudo
                $keyword = str_replace("@","",$keyword);
                $key = array_search($keyword,$search_settings->pseudos);
                if ($key === null){
                    return new JsonResponse(['method'=>'Could not find keyword '.$keyword],400);
                }
                //array_slice($search_settings->pseudos,$key,1);
                unset($search_settings->pseudos[$key]);
                $search_settings->pseudos = array_values($search_settings->pseudos);
                $search_settings = serialize($search_settings);
                $account = $this->getUser()->getActuelAccount();
                $account->setSearchSettings($search_settings);
                $em->persist($account);
                $em->flush();
                $history = new History();
                $history->setType('searchSet');
                $history->setMessage('Deleted @'.$keyword.' as a keyword !');
                $history->setFromAccount($account);
                $history->setDate(new \DateTime());
                $em->persist($history);
                $em->flush();
                return new JsonResponse(['output'=>'Successfully deleted: '.$keyword,'search_settings'=>$search_settings,'key'=>$key],200);
            }
            if (strpos($keyword,"#")===0) { // if contains # then hashtag
                $keyword = str_replace("#","",$keyword);
                $key = array_search($keyword,$search_settings->hashtags);
                if ($key === null){
                    return new JsonResponse(['method'=>'Could not find keyword '.$keyword],400);
                }
                //array_slice($search_settings->pseudos,$key,1);
                unset($search_settings->hashtags[$key]);
                $search_settings->hashtags = array_values($search_settings->hashtags);
                $search_settings = serialize($search_settings);
                $account = $this->getUser()->getActuelAccount();
                $account->setSearchSettings($search_settings);
                $em->persist($account);
                $em->flush();
                $history = new History();
                $history->setType('searchSet');
                $history->setMessage('Deleted #'.$keyword.' as a keyword !');
                $history->setFromAccount($account);
                $history->setDate(new \DateTime());
                $em->persist($history);
                $em->flush();
                return new JsonResponse(['output'=>'Successfully deleted: '.$keyword,'search_settings'=>$search_settings,'key'=>$key],200);
            }
            }
        return new JsonResponse(['method'=>'No declared method','output'=> serialize($search_settings)],400);
    }

    /**
     * @Route("/ajax/acc", name="ajax_acc")
     */
    public function ajaxAccount(Request $req){
        if($req->isMethod("DELETE")){
            $pseudo = $req->request->get('pseudo');
            $result = $this->getDoctrine()
                ->getRepository(Account::class)
                ->selectAccount($pseudo);
            if(!$result){
                return new JsonResponse(['output'=>'No results'],400);
            }
            $em = $this->getDoctrine()->getManager();
            $em->remove($result);
            $em->flush();
            return new JsonResponse(['output'=>'Successfully deleted: '.$pseudo],200);
        }
        return new JsonResponse(['output'=>'Method not allowed'],400);
    }

    /**
     * @Route("/ajax/proxy",name="ajax_proxy")
     */
    public function ajaxProxy(Request $req){
        if($req->isMethod("POST")){
            $proxy = $req->request->get('proxy');
            /*
            if(trim($proxy) == ""){ // TODO && not : (proxy format)
                return new JsonResponse(['output'=>'Proxy field empty'],400);
            }*/
            $account = $this->getUser()->getActuelAccount();
            if(trim($proxy) == ""){
                $account->setProxy(null);
                $rep = 'Successfully re-init';
            }
            else{
                $account->setProxy($proxy);
                $rep = 'Successfully added proxy: ';
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($account);
            $em->flush();
            return new JsonResponse(['output'=>$rep.$proxy],200);
        }
    }

}
