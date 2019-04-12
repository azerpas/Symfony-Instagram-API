<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController{
    /**
     * @Route("/", name="home" , methods="GET")
     */
    public function homePage(){
        return $this->render('home/home.html.twig');
    }
    /**
     * @Route("/features", name="features", methods={"GET"})
     */
    public function featuresPage(){
        return $this->render('home/features.html.twig');
    }
    /**
     * @Route("/contact", name="contact", methods={"GET"})
     */
    public function contactPage(){
        return $this->render('home/contact.html.twig');
    }
}