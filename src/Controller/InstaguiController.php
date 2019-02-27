<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class InstaguiController extends Controller
{
    /**
     * @Route("/instagui/home", name="inst_home")
     */
    public function home_page()
    {
        return $this->render('instagui/home.html.twig', [
            'controller_name' => 'InstaguiController','page'=> 'home'
        ]);
    }
    
    /**
     * @Route("/instagui/bots", name="inst_bots")
     */
    public function bots_page()
    {
        return $this->render('instagui/bots.html.twig', ['controller_name' => 'InstaguiController','page'=> 'bots']);
    }

    /**
     * @Route("/instagui/charts", name="inst_charts")
     */
    public function charts_page()
    {
        return $this->render('instagui/stat.html.twig', [
            'controller_name' => 'InstaguiController','page'=> 'statistiques'
        ]);
    }
}
