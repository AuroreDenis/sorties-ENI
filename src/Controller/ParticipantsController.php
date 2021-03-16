<?php

namespace App\Controller;

use App\Entity\Participants;
use App\Form\ParticipantsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ParticipantsController extends AbstractController
{
    /**
     * @Route("/", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->render('home/index.html.twig', []);
            // return $this->redirectToRoute('home');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route ("/participant/add", name="creer_un_admin")
     */

    public function add(EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $encoder)
    {
        $participant=new Participants();
        $registerForm=$this->createForm(ParticipantsType::class, $participant);
        $registerForm->handleRequest($request);
        if ($registerForm->isSubmitted() and $registerForm->isValid()) {

            //hasher le mot de passe avec class passwordEncoderInterface
            $hashed=$encoder->encodePassword($participant,$participant->getPassword());
            $participant->setPassword($hashed);

            //sauvegarder mon utilsateur
            //try{
                $em->persist($participant);
                $em->flush();
                $this->addFlash('success', 'le compte a été créé avec succès (veuillez-vous connecter maintenant)');
                return $this->redirectToRoute('home', [
                    'controller_name' => 'HomeController',
                ]);

            //} catch (\Doctrine\DBAL\Exception $e) {
            //    $errorMessage = $e->getMessage();
             //   echo ($errorMessage);
              //  $this->addFlash('error', 'Nous n\' avons pas pu créer le compte (email existant...etc)');
               // return $this->redirectToRoute('home', [
                 //   'controller_name' => 'HomeController',
              //  ]);
           // }

        }
        return $this->render('user/register.html.twig', [
            'controller_name' => 'UserController', "registerForm"=>$registerForm->createView()
        ]);
    }
}