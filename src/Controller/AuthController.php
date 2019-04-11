<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AuthController extends Controller{

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder){
        $user = new User();
        $form = $this->createForm(UserType::class,$user)
            ->add('save', SubmitType::class, ['label' => 'Create Account','attr' => ['class' => 'btn btn-lg btn-dark m-3']]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);
            $user->setAccounts(null);
            // To add, check if username already taken, then render page with every arguments with changing username
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('app_login');
        }
        return $this->render('instagui/register.html.twig', ['form'=>$form->createView()]);
    }

    /**
     * @Route("/login_v1", name="login")
     */
    public function login(Request $request){
        // use make:auth
    }

}